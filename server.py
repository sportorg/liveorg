import random
import string
import sys
import os
import json
from datetime import timedelta
import time

from flask import Flask, make_response, request, current_app, jsonify
from functools import update_wrapper

from itsdangerous import JSONWebSignatureSerializer as TokenSerializer, BadSignature


def is_executable():
    return hasattr(sys, 'frozen')


def module_path():
    if is_executable():
        return os.path.dirname(
            sys.executable
        )

    return os.path.abspath(os.path.join(os.path.dirname(__file__)))


BASE_DIR = module_path()


def base_dir(*paths):
    return os.path.join(BASE_DIR, *paths)


def cross_domain(origin=None, methods=None, headers=None,
                 max_age=21600, attach_to_all=True,
                 automatic_options=True):
    if methods is not None:
        methods = ', '.join(sorted(x.upper() for x in methods))
    if headers is not None and not isinstance(headers, str):
        headers = ', '.join(x.upper() for x in headers)
    if not isinstance(origin, str):
        origin = ', '.join(origin)
    if isinstance(max_age, timedelta):
        max_age = max_age.total_seconds()

    def get_methods():
        if methods is not None:
            return methods

        options_resp = current_app.make_default_options_response()
        return options_resp.headers['allow']

    def decorator(f):
        def wrapped_function(*args, **kwargs):
            if automatic_options and request.method == 'OPTIONS':
                resp = current_app.make_default_options_response()
            else:
                resp = make_response(f(*args, **kwargs))
            if not attach_to_all and request.method != 'OPTIONS':
                return resp

            h = resp.headers

            h['Access-Control-Allow-Origin'] = origin
            h['Access-Control-Allow-Methods'] = get_methods()
            h['Access-Control-Max-Age'] = str(max_age)
            if headers is not None:
                h['Access-Control-Allow-Headers'] = headers
            return resp

        f.provide_automatic_options = False
        return update_wrapper(wrapped_function, f)

    return decorator


app = Flask(__name__, static_url_path='', static_folder='public')

token_serializer = TokenSerializer('secret')


@app.route('/api/events', methods=['POST'])
@cross_domain(origin='*')
def post_event():
    event_id = str(round(time.time())) + ''.join(random.choices(string.ascii_lowercase + string.digits, k=6))
    return jsonify({
        'id': str(event_id),
        'token': token_serializer.dumps({
            'id': event_id
        }).decode('utf-8')
    })


@app.route('/api/events/<token>', methods=['POST'])
@cross_domain(origin='*')
def post_events(token):
    try:
        data = token_serializer.loads(token)
    except BadSignature as e:
        return jsonify({'error': str(e)}), 403
    if 'id' not in data:
        return jsonify({'error': 'Error'}), 400
    file_path = base_dir('public', 'events', str(data['id']) + '.json')
    obj = json.loads(request.data)
    if os.path.isfile(file_path):
        if obj['object'] != 'Race':
            with open(file_path, 'r+') as f:
                old_obj = json.load(f)
                # TODO
                obj = old_obj
    else:
        if obj['object'] != 'Race':
            return jsonify({'error': 'Race not found'}), 404
    with open(file_path, 'w+') as f:
        json.dump(obj, f)
    return jsonify({
        'status': 'ok'
    })


@app.route('/api/events/<token>', methods=['DELETE'])
@cross_domain(origin='*')
def delete_events(token):
    try:
        data = token_serializer.loads(token)
    except BadSignature as e:
        return jsonify({'error': str(e)}), 403
    if 'id' not in data:
        return jsonify({'error': 'Error'}), 400
    # TODO
    return jsonify({
        'status': 'ok'
    })


@app.route('/')
@cross_domain(origin='*')
def root():
    return app.send_static_file('index.html')


@app.route('/admin')
@cross_domain(origin='*')
def admin():
    return app.send_static_file('admin.html')


if __name__ == '__main__':
    app.run()
