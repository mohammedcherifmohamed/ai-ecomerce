import requests

from app.core.config import settings

class LaravelAPI : 
    def __init__(self):
            self.base_url = settings.LARAVEL_API_URL.rstrip('/')
            
            self.headers = {
                "Accept": "application/json",
                "Content-Type":"application/json",
                "X-AI-KEY":settings.AI_API_KEY, 
            }
    
    def post(self,endpoint:str,payload:dict) -> dict:
        
        response = requests.post(
            url=f"{self.base_url}/{endpoint.lstrip('/')}",
            json=payload,
            headers=self.headers,
            timeout=10,
        )
        
        response.raise_for_status()
        
        return response.json()