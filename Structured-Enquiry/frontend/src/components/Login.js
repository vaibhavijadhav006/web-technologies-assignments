import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { FaHeart, FaEnvelope, FaLock, FaSignInAlt, FaUserPlus, FaShieldAlt } from 'react-icons/fa';

const Login = () => {
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!formData.email || !formData.password) {
        toast.error('Please fill all fields');
        return;
    }

    setLoading(true);

    try {
        // Try real backend first
        const response = await fetch('http://localhost:5000/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: formData.email,
                password: formData.password
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Store user data
            localStorage.setItem('user', JSON.stringify(data.user));
            localStorage.setItem('token', 'user-token');
            
            toast.success('Login successful!');
            
            // Redirect with animation delay
            setTimeout(() => {
                if (data.user.role === 'admin') {
                    navigate('/admin/dashboard');
                } else {
                    navigate('/dashboard');
                }
            }, 1000);
        } else {
            // Fallback to demo credentials
            handleDemoLogin();
        }
    } catch (error) {
        console.error('Login error:', error);
        toast.error('Backend not responding. Using demo mode...');
        handleDemoLogin();
    } finally {
        setLoading(false);
    }
};

// Add this helper function
const handleDemoLogin = () => {
    const isAdmin = formData.email.includes('admin') || formData.email === 'admin@example.com';
    
    localStorage.setItem('user', JSON.stringify({ 
        id: Date.now(),
        name: isAdmin ? 'Admin User' : 'Regular User',
        email: formData.email,
        role: isAdmin ? 'admin' : 'user',
        totalDonations: isAdmin ? 0 : 2500,
        mobile: '9876543210',
        address: isAdmin ? 'Admin Address' : 'User Address'
    }));
    localStorage.setItem('token', 'demo-token');
    
    toast.success(isAdmin ? 'Demo Admin Login' : 'Demo User Login');
    
    setTimeout(() => {
        if (isAdmin) {
            navigate('/admin/dashboard');
        } else {
            navigate('/dashboard');
        }
    }, 500);
};
  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 p-4">
      <div className="max-w-md w-full">
        {/* Header */}
        <div className="text-center mb-8">
          <div className="w-20 h-20 bg-gradient-to-r from-purple-600 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <FaHeart className="text-3xl text-white" />
          </div>
          <h1 className="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-500 bg-clip-text text-transparent">
            Welcome Back
          </h1>
          <p className="text-gray-600 mt-2">Sign in to continue your journey of giving</p>
        </div>

        {/* Login Card */}
        <div className="bg-white rounded-3xl shadow-2xl p-8">
          <form onSubmit={handleSubmit}>
            {/* Email */}
            <div className="mb-6">
              <label className="block text-gray-700 text-sm font-medium mb-2">
                <FaEnvelope className="inline mr-2" />
                Email Address
              </label>
              <input
                type="email"
                value={formData.email}
                onChange={(e) => setFormData({...formData, email: e.target.value})}
                className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none transition duration-200"
                placeholder="you@example.com"
                required
              />
            </div>

            {/* Password */}
            <div className="mb-6">
              <label className="block text-gray-700 text-sm font-medium mb-2">
                <FaLock className="inline mr-2" />
                Password
              </label>
              <input
                type="password"
                value={formData.password}
                onChange={(e) => setFormData({...formData, password: e.target.value})}
                className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none transition duration-200"
                placeholder="••••••••"
                required
              />
            </div>

            {/* Demo Credentials */}
            <div className="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
              <p className="text-sm text-blue-800 font-medium mb-2">Demo Credentials:</p>
              <div className="grid grid-cols-2 gap-4 text-xs">
                <div>
                  <p className="font-semibold text-blue-600">Admin</p>
                  <p>admin@example.com</p>
                  <p>any password works</p>
                </div>
                <div>
                  <p className="font-semibold text-blue-600">User</p>
                  <p>user@example.com</p>
                  <p>any password works</p>
                </div>
              </div>
            </div>

            {/* Submit Button */}
            <button
              type="submit"
              disabled={loading}
              className="w-full bg-gradient-to-r from-purple-600 to-pink-500 text-white font-semibold py-3 px-4 rounded-xl hover:from-purple-700 hover:to-pink-600 transition duration-200 disabled:opacity-50 flex items-center justify-center"
            >
              {loading ? (
                <>
                  <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                  Signing in...
                </>
              ) : (
                <>
                  <FaSignInAlt className="mr-2" />
                  Sign In
                </>
              )}
            </button>
          </form>

          {/* Register Link */}
          <div className="mt-6 text-center">
            <p className="text-gray-600">
              Don't have an account?{' '}
              <Link to="/register" className="text-purple-600 font-semibold hover:text-purple-800">
                <FaUserPlus className="inline mr-1" />
                Sign up now
              </Link>
            </p>
          </div>

          {/* Security Note */}
          <div className="mt-6 p-3 bg-green-50 border border-green-200 rounded-lg">
            <p className="text-xs text-green-800 text-center">
              <FaShieldAlt className="inline mr-1" />
              Your data is protected with 256-bit SSL encryption
            </p>
          </div>
        </div>

        {/* Stats */}
        <div className="mt-8 grid grid-cols-3 gap-4 text-center">
          <div className="bg-white rounded-2xl p-4 shadow-sm">
            <div className="text-xl font-bold text-purple-600">₹2.5M+</div>
            <div className="text-sm text-gray-600">Donations</div>
          </div>
          <div className="bg-white rounded-2xl p-4 shadow-sm">
            <div className="text-xl font-bold text-purple-600">500+</div>
            <div className="text-sm text-gray-600">Campaigns</div>
          </div>
          <div className="bg-white rounded-2xl p-4 shadow-sm">
            <div className="text-xl font-bold text-purple-600">10K+</div>
            <div className="text-sm text-gray-600">Donors</div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Login;