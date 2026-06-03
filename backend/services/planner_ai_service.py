import os
import time
import json
from typing import List, Dict, Any
from google import genai
from google.genai import types
from pydantic import BaseModel, Field
from dotenv import load_dotenv

# Load environment variables from .env
load_dotenv()

# Initialize the Gemini Client
# The SDK automatically checks for the GEMINI_API_KEY environment variable.
api_key = os.getenv("GEMINI_API_KEY")
client = genai.Client(api_key=api_key)


# =====================================================================
# Pydantic Schemas to Enforce the Frontend's Exact JSON Structure
# =====================================================================

class TimelineDay(BaseModel):
    day: int
    country: str
    destination: str = Field(
        description="The primary city, town, region, or landmark for this day, e.g. Oslo, Tromso, Lapland, or Geirangerfjord."
    )
    imageSearchQuery: str = Field(
        description="A precise clean photo search query for this day's destination and season, e.g. Oslo winter city or Tromso northern lights. Do not include quotes or punctuation."
    )
    items: List[str]

class BudgetEstimate(BaseModel):
    stay: float
    food: float
    transport: float
    activities: float
    emergency: float = 0
    total: float
    perPerson: float
    dailyAverage: float
    currency: str = "USD"
    tips: List[str] = Field(
        description="Short practical budget saving tips based on accommodation, transport, country, pax, and budget tier."
    )

class PackingItem(BaseModel):
    id: int
    name: str
    checked: bool = False

class WeatherForecastItem(BaseModel):
    temp: float = Field(description="Average expected temperature in Celsius")
    condition: str = Field(description="Expected condition like Snow, Sunny, Rainy, etc.")

class AuroraForecast(BaseModel):
    kp: str = Field(description="Expected Kp index range or number, e.g., '4.2' or '3-4'")
    chance: int = Field(description="Percentage chance of seeing the aurora, or 0 if not applicable")
    window: str = Field(description="Best time window to view, or 'N/A' if not applicable")

class TravelPlanData(BaseModel):
    timelineDays: List[TimelineDay]
    budgetEstimate: BudgetEstimate
    packingList: List[PackingItem]
    weatherForecast: List[WeatherForecastItem]
    auroraForecast: AuroraForecast
    summary: str


class PlannerAIService:
    @staticmethod
    def generate_travel_plan(preferences: Dict[str, Any]) -> Dict[str, Any]:
        """
        Generates a structured travel plan based on user preferences using Gemini.
        Includes an automatic retry mechanism to survive free-tier rate limits (429).
        """
        # Extract fields with safe defaults
        countries = ", ".join(preferences.get("countryRoute", []))
        start_date = preferences.get("startDate", "")
        end_date = preferences.get("endDate", "")
        duration = preferences.get("duration", 5)
        budget = preferences.get("budget", "Medium")
        season = preferences.get("season", "Winter")
        pax = preferences.get("pax", 1)
        style = preferences.get("style", "Adventure")
        interests = ", ".join(preferences.get("interests", []))
        trip_type = preferences.get("tripType", "Couple")
        transport = preferences.get("transport", "Train")
        accommodation = preferences.get("accommodation", "Hotel")
        activity_level = preferences.get("activityLevel", "Moderate")

        # Construct a robust prompt leveraging all incoming preferences
        prompt = f"""
        You are an expert AI travel planner for the application Beyond66. 
        Create a comprehensive, realistic travel plan based strictly on these constraints:
        
        - Country Route: {countries}
        - Start Date: {start_date}
        - End Date: {end_date}
        - Duration: {duration} days
        - Budget Tier: {budget}
        - Season: {season}
        - Number of Travelers (Pax): {pax}
        - Travel Style: {style}
        - Key Interests: {interests}
        - Trip Type: {trip_type}
        - Preferred Transport: {transport}
        - Preferred Accommodation: {accommodation}
        - Physical Activity Level: {activity_level}

        Requirements:
        1. Create a detailed daily timeline for exactly {duration} days from {start_date} to {end_date}, distributed reasonably among the countries: {countries}.
        2. For each day, identify the specific destination city, town, region, or landmark.
        3. For each day, provide a clean imageSearchQuery suitable for Unsplash or Pexels landscape photography. The query should include destination plus season or key visual, with no quotes or punctuation.
        4. Provide a realistic AI-recommended market budget estimate in USD for {pax} person(s). Do not force the estimate to match the selected budget tier. Calculate it mainly from country route, duration, pax, season, accommodation, transport, travel style, activity level, and interests. Treat the selected Budget Tier ({budget}) only as the user's affordability preference for budget tips and comparison. Include stay, food, transport, activities, emergency, total, perPerson, dailyAverage, currency, and tips. The total must equal stay + food + transport + activities + emergency. The budget will be shown as the AI recommendation, while users can manually adjust each category in the frontend.
        Tips should explain whether the realistic recommended estimate is above, close to, or below the user's selected {budget} preference and how the user can reduce or adjust the budget.
        5. Curate a relevant packing list itemizing critical items for {season} weather, a travel style of {style}, and interests like {interests}. Assign ascending incremental IDs starting from 1.
        6. Provide historical average weather forecast parameters for the chosen destination route during {season}.
        7. If the route covers northern/aurora latitudes (like Norway or Sweden) and the season is Winter, provide realistic Aurora metrics. If the location/season makes Aurora viewing impossible, set chance to 0 and window to 'N/A'.
        8. Keep the summary engaging and tailored to a {trip_type} trip.
        """

        # Free tier safety: Retry up to 3 times if we get throttled by a 429 error
        max_retries = 3
        initial_delay = 4  # seconds

        for attempt in range(max_retries):
            try:
                # Call Gemini using Structured Outputs via gemini-2.5-flash-lite
                response = client.models.generate_content(
                    model='gemini-2.5-flash-lite',  # High-quota free model
                    contents=prompt,
                    config=types.GenerateContentConfig(
                        response_mime_type="application/json",
                        response_schema=TravelPlanData,
                        temperature=0.3,
                    ),
                )
                
                # The response.text is guaranteed to be a valid JSON string matching TravelPlanData.
                return json.loads(response.text)

            except Exception as e:
                error_msg = str(e)
                # Check if it's a rate limit / quota exhaustion event
                if "429" in error_msg or "RESOURCE_EXHAUSTED" in error_msg:
                    if attempt < max_retries - 1:
                        # Exponential backoff: sleep 4s, then 8s before trying again
                        sleep_time = initial_delay * (2 ** attempt)
                        time.sleep(sleep_time)
                        continue
                
                # Raise the error if it's not a 429 or if we've exhausted our retries
                raise RuntimeError(f"Gemini API generation failed after {attempt + 1} attempts: {error_msg}")