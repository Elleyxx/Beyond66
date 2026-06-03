from flask import Flask, jsonify
from flask_cors import CORS
from routes.planner_ai_routes import planner_ai_bp

app = Flask(__name__)
CORS(app)

# Registering with prefix maps the route cleanly to /api/planner/ai/generate
app.register_blueprint(planner_ai_bp, url_prefix='/api/planner')


@app.get('/api/health')
def health():
    return jsonify({"success": True, "status": "ai-ok"})

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=False, use_reloader=False)
