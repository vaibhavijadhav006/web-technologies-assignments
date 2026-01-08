const API_URL = 'http://localhost:5000/api';

// Simple API helper
export const api = {
  // Auth
  register: (userData) => 
    fetch(`${API_URL}/auth/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(userData)
    }).then(res => res.json()),
  
  login: (credentials) => 
    fetch(`${API_URL}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(credentials)
    }).then(res => res.json()),
  
  // Users
  getUsers: () => 
    fetch(`${API_URL}/users`).then(res => res.json()),
  
  // Campaigns
  getCampaigns: () => 
    fetch(`${API_URL}/campaigns`).then(res => res.json()),
  
  createCampaign: (campaignData) =>
    fetch(`${API_URL}/campaigns`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(campaignData)
    }).then(res => res.json()),
  
  updateCampaign: (id, campaignData) =>
    fetch(`${API_URL}/campaigns/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(campaignData)
    }).then(res => res.json()),
  
  deleteCampaign: (id) =>
    fetch(`${API_URL}/campaigns/${id}`, {
      method: 'DELETE'
    }).then(res => res.json()),
  
  // Donations
  donate: (donationData) =>
    fetch(`${API_URL}/donate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(donationData)
    }).then(res => res.json()),
  
  // User Profile
  getUserProfile: (userId) =>
    fetch(`${API_URL}/user/${userId}`).then(res => res.json()),
  
  // Admin
  getAdminDashboard: () =>
    fetch(`${API_URL}/admin/dashboard`).then(res => res.json())
};

export default api;
