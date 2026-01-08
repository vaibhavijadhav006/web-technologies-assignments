import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import './App.css';

// Import components (make sure these files exist)
import Login from './components/Login';
import Register from './components/Register';
import Dashboard from './components/Dashboard';
import AdminDashboard from './components/AdminDashboard';
import UserProfile from './components/UserProfile';
import CampaignList from './components/CampaignList';
import LandingPage from './components/LandingPage';

// Simple Private Route Component
const PrivateRoute = ({ children }) => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  
  if (!user.email) {
    toast.error('Please login first');
    return <Navigate to="/login" />;
  }
  
  return children;
};

// Simple Admin Route Component
const AdminRoute = ({ children }) => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  
  if (!user.email) {
    toast.error('Please login first');
    return <Navigate to="/login" />;
  }
  
  if (user.role !== 'admin') {
    toast.error('Admin access required');
    return <Navigate to="/dashboard" />;
  }
  
  return children;
};

function App() {
  return (
    <Router>
      <div className="App">
        <ToastContainer
          position="top-right"
          autoClose={3000}
          hideProgressBar={false}
          newestOnTop
          closeOnClick
          rtl={false}
          pauseOnFocusLoss
          draggable
          pauseOnHover
          theme="light"
        />
        
        <Routes>
          {/* Default route - Landing page */}
          <Route path="/" element={<LandingPage />} />
          
          {/* Public routes */}
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          
          {/* Protected User routes */}
          <Route path="/dashboard" element={
            <PrivateRoute>
              <Dashboard />
            </PrivateRoute>
          } />
          
          <Route path="/profile" element={
            <PrivateRoute>
              <UserProfile />
            </PrivateRoute>
          } />
          
          <Route path="/campaigns" element={
            <PrivateRoute>
              <CampaignList />
            </PrivateRoute>
          } />
          
          {/* Admin routes */}
          <Route path="/admin/dashboard" element={
            <AdminRoute>
              <AdminDashboard />
            </AdminRoute>
          } />
          
          {/* Catch all - redirect to landing */}
          <Route path="*" element={<Navigate to="/" />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;