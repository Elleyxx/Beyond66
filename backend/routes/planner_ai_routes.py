import os
from flask import Blueprint, request, jsonify
from services.planner_ai_service import PlannerAIService

planner_ai_bp = Blueprint('planner_ai', __name__)

# Keep MOCK_MODE = True while building your Vue components/CSS.
# Switch to False only when verifying real Gemini output pipelines.
MOCK_MODE = True  

@planner_ai_bp.route('/ai/generate', methods=['POST'])
def generate_plan():
    try:
        # 1. Catch the frontend payload
        data = request.get_json()
        if not data:
            return jsonify({"success": False, "error": "Invalid JSON or missing payload"}), 400

        # 2. Free Tier Shield: If you are styling UI, bypass the API completely
        if MOCK_MODE:
            return jsonify({
                "success": True,
                "data": {
                    "timelineDays": [
                        {
                            "day": 1,
                            "country": data.get("countryRoute", ["Norway"])[0],
                            "destination": "Oslo",
                            "imageSearchQuery": "Oslo winter skyline",
                            "items": ["Arrive safely", "Check into accommodation"]
                        }
                    ],
                    "budgetEstimate": {
                        "stay": 400, "food": 150, "transport": 200, "activities": 100, "emergency": 50,
                        "total": 900, "perPerson": 900, "dailyAverage": 180, "currency": "USD",
                        "tips": ["Using free mock data to preserve your daily API token limits!"]
                    },
                    "packingList": [{"id": 1, "name": "Heavy winter jacket", "checked": False}],
                    "weatherForecast": [{"temp": -3.0, "condition": "Snow"}],
                    "auroraForecast": {"kp": "4.0", "chance": 65, "window": "21:00 - 01:00"},
                    "summary": "This is sandbox local mock data. Switch MOCK_MODE to False to fetch live plans."
                }
            }), 200

        # 3. If MOCK_MODE is False, execute the resilient Flash-Lite Service
        travel_plan = PlannerAIService.generate_travel_plan(data)
        return jsonify({
            "success": True,
            "data": travel_plan
        }), 200

    except RuntimeError as re:
        return jsonify({"success": False, "error": str(re)}), 429
    except Exception as e:
        return jsonify({"success": False, "error": f"Internal Server Error: {str(e)}"}), 500