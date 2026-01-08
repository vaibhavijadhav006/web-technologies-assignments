import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { 
  FaUser, 
  FaEnvelope, 
  FaLock, 
  FaPhone, 
  FaHome, 
  FaUserShield,
  FaUserTag,
  FaUserPlus,
  FaArrowLeft
} from 'react-icons/fa';

const Register = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    mobile: '',
    address: '',
    role: 'user' // Default to user
  });
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (formData.password !== formData.confirmPassword) {
        toast.error('Passwords do not match');
        return;
    }

    if (formData.password.length < 6) {
        toast.error('Password must be at least 6 characters');
        return;
    }

    setLoading(true);

    try {
        // Register API call
        const response = await fetch('http://localhost:5000/api/auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: formData.name,
                email: formData.email,
                password: formData.password,
                mobile: formData.mobile,
                address: formData.address,
                role: formData.role
            })
        });

        const data = await response.json();
        
        if (data.success) {
            toast.success('Registration successful!');
            
            // AUTO LOGIN after successful registration
            const loginResponse = await fetch('http://localhost:5000/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: formData.email,
                    password: formData.password
                })
            });

            const loginData = await loginResponse.json();
            
            if (loginData.success) {
                // Store user data
                localStorage.setItem('user', JSON.stringify(loginData.user));
                localStorage.setItem('token', 'user-token'); // For demo
                
                toast.success('Auto-login successful!');
                
                // Redirect based on role
                setTimeout(() => {
                    if (loginData.user.role === 'admin') {
                        navigate('/admin/dashboard');
                    } else {
                        navigate('/dashboard');
                    }
                }, 1000);
            } else {
                toast.error('Registration successful but auto-login failed. Please login manually.');
                navigate('/login');
            }
        } else {
            toast.error(data.message || 'Registration failed');
        }
    } catch (error) {
        console.error('Registration error:', error);
        toast.error('Network error. Please try again.');
    } finally {
        setLoading(false);
    }
};
  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 p-4">
      <div className="max-w-2xl w-full">
        {/* Header */}
        <div className="text-center mb-8">
          <div className="w-20 h-20 bg-gradient-to-r from-purple-600 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <FaUserPlus className="text-3xl text-white" />
          </div>
          <h1 className="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-500 bg-clip-text text-transparent">
            Create Your Account
          </h1>
          <p className="text-gray-600 mt-2">Join our community</p>
        </div>

        <div className="bg-white rounded-3xl shadow-2xl p-8">
          <form onSubmit={handleSubmit}>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* Name */}
              <div>
                <label className="block text-gray-700 text-sm font-medium mb-2">
                  <FaUser className="inline mr-2" />
                  Full Name
                </label>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => setFormData({...formData, name: e.target.value})}
                  className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                  placeholder="John Doe"
                  required
                />
              </div>

              {/* Email */}
              <div>
                <label className="block text-gray-700 text-sm font-medium mb-2">
                  <FaEnvelope className="inline mr-2" />
                  Email Address
                </label>
                <input
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({...formData, email: e.target.value})}
                  className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                  placeholder="you@example.com"
                  required
                />
              </div>

              {/* Password */}
              <div>
                <label className="block text-gray-700 text-sm font-medium mb-2">
                  <FaLock className="inline mr-2" />
                  Password
                </label>
                <input
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData({...formData, password: e.target.value})}
                  className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                  placeholder="••••••••"
                  required
                />
              </div>

              {/* Confirm Password */}
              <div>
                <label className="block text-gray-700 text-sm font-medium mb-2">
                  <FaLock className="inline mr-2" />
                  Confirm Password
                </label>
                <input
                  type="password"
                  value={formData.confirmPassword}
                  onChange={(e) => setFormData({...formData, confirmPassword: e.target.value})}
                  className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                  placeholder="••••••••"
                  required
                />
              </div>

              {/* Mobile */}
              <div>
                <label className="block text-gray-700 text-sm font-medium mb-2">
                  <FaPhone className="inline mr-2" />
                  Mobile Number
                </label>
                <input
                  type="tel"
                  value={formData.mobile}
                  onChange={(e) => setFormData({...formData, mobile: e.target.value})}
                  className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                  placeholder="9876543210"
                  required
                />
              </div>

              {/* Role Selection - IMPORTANT FOR ADMIN */}
              <div>
                <label className="block text-gray-700 text-sm font-medium mb-2">
                  <FaUserTag className="inline mr-2" />
                  Register As
                </label>
                <div className="grid grid-cols-2 gap-3">
                  <button
                    type="button"
                    onClick={() => setFormData({...formData, role: 'user'})}
                    className={`py-3 rounded-xl border-2 transition ${formData.role === 'user' ? 'border-purple-500 bg-purple-50 text-purple-600' : 'border-gray-300 text-gray-600 hover:border-purple-300'}`}
                  >
                    <FaUser className="inline mr-2" />
                    Regular User
                  </button>
                  <button
                    type="button"
                    onClick={() => setFormData({...formData, role: 'admin'})}
                    className={`py-3 rounded-xl border-2 transition ${formData.role === 'admin' ? 'border-purple-500 bg-purple-50 text-purple-600' : 'border-gray-300 text-gray-600 hover:border-purple-300'}`}
                  >
                    <FaUserShield className="inline mr-2" />
                    Admin
                  </button>
                </div>
              </div>
            </div>

            {/* Address */}
            <div className="mt-6">
              <label className="block text-gray-700 text-sm font-medium mb-2">
                <FaHome className="inline mr-2" />
                Address
              </label>
              <textarea
                value={formData.address}
                onChange={(e) => setFormData({...formData, address: e.target.value})}
                className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                rows="3"
                placeholder="Enter your complete address"
                required
              />
            </div>

            {/* Submit Button */}
            <button
              type="submit"
              disabled={loading}
              className="w-full mt-8 bg-gradient-to-r from-purple-600 to-pink-500 text-white font-semibold py-3 px-4 rounded-xl hover:from-purple-700 hover:to-pink-600 transition disabled:opacity-50 flex items-center justify-center"
            >
              {loading ? (
                <>
                  <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                  Creating Account...
                </>
              ) : (
                <>
                  <FaUserPlus className="mr-2" />
                  Create Account
                </>
              )}
            </button>
          </form>

          {/* Login Link */}
          <div className="mt-6 text-center">
            <p className="text-gray-600">
              Already have an account?{' '}
              <a href="/login" className="text-purple-600 font-semibold hover:text-purple-800">
                <FaArrowLeft className="inline mr-1" />
                Sign in here
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Register;