import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import {
  FaUser,
  FaEnvelope,
  FaPhone,
  FaHome,
  FaRupeeSign,
  FaHandHoldingHeart,
  FaCalendarAlt,
  FaEdit,
  FaSave,
  FaTimes,
  FaLock,
  FaTrash,
  FaUsers,
  FaChartLine,
  FaHistory,
  FaHeart,
  FaSignOutAlt,
  FaArrowLeft
} from 'react-icons/fa';

const UserProfile = () => {
  const navigate = useNavigate();
  const [user, setUser] = useState(null);
  const [donations, setDonations] = useState([]);
  const [editing, setEditing] = useState(false);
  const [editForm, setEditForm] = useState({});
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadUserData();
  }, []);

  const loadUserData = async () => {
    try {
      setLoading(true);
      
      // Get user from localStorage
      const userData = localStorage.getItem('user');
      console.log('Raw user data from localStorage:', userData);
      
      if (!userData) {
        toast.error('Please login first');
        navigate('/login');
        return;
      }

      const parsedUser = JSON.parse(userData);
      console.log('Parsed user:', parsedUser);
      
      if (!parsedUser || !parsedUser.email) {
        toast.error('Invalid user data');
        navigate('/login');
        return;
      }

      // Try to fetch from backend
      try {
        const userId = parsedUser.id || parsedUser._id;
        if (userId) {
          // Fetch user profile
          const response = await fetch(`http://localhost:5000/api/user/${userId}`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
          });

          if (response.ok) {
            const data = await response.json();
            if (data.success && data.user) {
              // Update localStorage with fresh data
              const updatedUser = { ...parsedUser, ...data.user };
              localStorage.setItem('user', JSON.stringify(updatedUser));
              setUser(updatedUser);
              
              // Fetch donations from DB - try multiple endpoints
              try {
                // Try with user ID in donations endpoint
                const donationsResponse = await fetch(`http://localhost:5000/api/donations/user`, {
                  method: 'GET',
                  headers: { 
                    'Content-Type': 'application/json'
                  }
                });
                
                if (donationsResponse.ok) {
                  const donationsData = await donationsResponse.json();
                  if (donationsData.success && donationsData.donations) {
                    const formattedDonations = donationsData.donations.map(d => ({
                      id: d._id || d.id,
                      campaignId: d.campaign?._id || d.campaign?.id,
                      campaignTitle: d.campaign?.title || 'General Donation',
                      amount: d.amount,
                      date: d.createdAt || d.donatedAt
                    }));
                    setDonations(formattedDonations);
                  }
                } else {
                  // Try using donations from user data
                  if (data.donations && Array.isArray(data.donations) && data.donations.length > 0) {
                    setDonations(data.donations.map(d => ({
                      id: d.id || d._id,
                      campaignTitle: 'General Donation',
                      amount: d.amount,
                      date: d.date || d.createdAt
                    })));
                  }
                }
              } catch (donationError) {
                console.error('Error fetching donations:', donationError);
                // Use donations from user data if available
                if (data.donations && Array.isArray(data.donations) && data.donations.length > 0) {
                  setDonations(data.donations.map(d => ({
                    id: d.id || d._id,
                    campaignTitle: 'General Donation',
                    amount: d.amount,
                    date: d.date || d.createdAt
                  })));
                } else {
                  // Fallback to localStorage
                  const userEmail = data.user.email;
                  const donationsKey = `donations_${userEmail}`;
                  const localDonations = localStorage.getItem(donationsKey);
                  if (localDonations) {
                    try {
                      const parsed = JSON.parse(localDonations);
                      setDonations(Array.isArray(parsed) ? parsed : []);
                    } catch (e) {
                      setDonations([]);
                    }
                  }
                }
              }
              
              // Initialize edit form
              setEditForm({
                name: data.user.name || 'User',
                email: data.user.email,
                mobile: data.user.mobile || '',
                address: data.user.address || ''
              });
              
              setLoading(false);
              return;
            }
          }
        }
      } catch (error) {
        console.error('Error fetching user from backend:', error);
      }

      // Fallback to localStorage data
      setUser(parsedUser);
      
      // Initialize edit form with user data
      setEditForm({
        name: parsedUser.name || 'User',
        email: parsedUser.email,
        mobile: parsedUser.mobile || '9876543210',
        address: parsedUser.address || 'Enter your address here'
      });

      // Load donations - check multiple possible keys
      const userEmail = parsedUser.email;
      console.log('Loading donations for email:', userEmail);
      
      // Try different possible keys
      const donationsKey = `donations_${userEmail}`;
      const altDonationsKey = `donations_${userEmail.toLowerCase()}`;
      
      let userDonations = localStorage.getItem(donationsKey);
      
      // If not found, try alternative key
      if (!userDonations) {
        userDonations = localStorage.getItem(altDonationsKey);
      }
      
      console.log('Donations found:', userDonations);
      
      if (userDonations) {
        try {
          const parsedDonations = JSON.parse(userDonations);
          console.log('Parsed donations:', parsedDonations);
          setDonations(Array.isArray(parsedDonations) ? parsedDonations : []);
        } catch (e) {
          console.error('Error parsing donations:', e);
          setDonations([]);
        }
      } else {
        console.log('No donations found, initializing empty array');
        setDonations([]);
        // Initialize donations in localStorage
        localStorage.setItem(donationsKey, JSON.stringify([]));
      }

    } catch (error) {
      console.error('Error loading user data:', error);
      toast.error('Error loading profile data');
    } finally {
      setLoading(false);
    }
  };

  const handleSaveProfile = async () => {
    try {
      if (!user) return;
      
      setLoading(true);
      
      const userId = user.id || user._id;
      
      // Try to save to database - use direct endpoint first
      if (userId) {
        try {
          // Try direct endpoint via /api/users/:id (no auth required)
          const response = await fetch(`http://localhost:5000/api/users/${userId}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              name: editForm.name,
              mobile: editForm.mobile,
              address: editForm.address
            })
          });

          if (!response.ok) {
            // If that fails, try /api/user/:id endpoint
            const altResponse = await fetch(`http://localhost:5000/api/user/${userId}`, {
              method: 'PUT',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                name: editForm.name,
                mobile: editForm.mobile,
                address: editForm.address
              })
            });

            if (altResponse.ok) {
              const altData = await altResponse.json();
              if (altData.success && altData.user) {
                const updatedUser = { 
                  ...user, 
                  ...altData.user
                };
                
                localStorage.setItem('user', JSON.stringify(updatedUser));
                setUser(updatedUser);
                setEditing(false);
                
                window.dispatchEvent(new Event('storage'));
                
                toast.success('Profile updated successfully in database!');
                setLoading(false);
                return;
              }
            }
            
            throw new Error(`HTTP error! status: ${response.status}`);
          }

          const data = await response.json();
          
          if (data.success && data.user) {
            // Update with server response
            const updatedUser = { 
              ...user, 
              ...data.user
            };
            
            // Save to localStorage
            localStorage.setItem('user', JSON.stringify(updatedUser));
            setUser(updatedUser);
            setEditing(false);
            
            // Update navbar by triggering storage event
            window.dispatchEvent(new Event('storage'));
            
            toast.success('Profile updated successfully in database!');
            setLoading(false);
            return;
          } else {
            toast.error(data.message || 'Failed to update profile');
          }
        } catch (error) {
          console.error('Error updating profile in backend:', error);
          // Don't show error toast here, will fallback to localStorage
        }
      }
      
      // Fallback to localStorage only if both endpoints fail
      const updatedUser = { 
        ...user, 
        ...editForm 
      };
      
      localStorage.setItem('user', JSON.stringify(updatedUser));
      setUser(updatedUser);
      setEditing(false);
      
      window.dispatchEvent(new Event('storage'));
      
      toast.warning('Profile updated locally. Database update failed. Please check your connection.');
    } catch (error) {
      console.error('Error saving profile:', error);
      toast.error('Error saving profile: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('user');
    localStorage.removeItem('token');
    toast.success('Logged out successfully');
    navigate('/login');
  };

  const handleDeleteAccount = () => {
    if (window.confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
      try {
        if (user && user.email) {
          // Remove user-specific data
          localStorage.removeItem(`donations_${user.email}`);
          localStorage.removeItem(`donations_${user.email.toLowerCase()}`);
          localStorage.removeItem(`volunteered_${user.email}`);
        }
        
        // Remove auth data
        localStorage.removeItem('user');
        localStorage.removeItem('token');
        
        // Trigger storage event to update navbar
        window.dispatchEvent(new Event('storage'));
        
        toast.success('Account deleted successfully');
        navigate('/login');
      } catch (error) {
        console.error('Error deleting account:', error);
        toast.error('Error deleting account');
      }
    }
  };

  const addTestDonation = () => {
    if (!user) {
      toast.error('No user found');
      return;
    }

    const testDonation = {
      id: Date.now(),
      campaignId: 'test-123',
      campaignTitle: 'Education for Children',
      amount: 1000,
      date: new Date().toISOString()
    };

    // Get current donations
    const donationsKey = `donations_${user.email}`;
    const currentDonations = JSON.parse(localStorage.getItem(donationsKey) || '[]');
    const updatedDonations = [...currentDonations, testDonation];
    
    // Save to localStorage
    localStorage.setItem(donationsKey, JSON.stringify(updatedDonations));
    setDonations(updatedDonations);
    
    toast.success('Test donation added! Refresh to see updated stats.');
  };

  const clearAllDonations = () => {
    if (!user) return;
    
    if (window.confirm('Clear all donation history?')) {
      const donationsKey = `donations_${user.email}`;
      localStorage.setItem(donationsKey, JSON.stringify([]));
      setDonations([]);
      toast.success('All donations cleared');
    }
  };

  // Calculate statistics
  const stats = {
    totalDonated: donations.reduce((sum, donation) => sum + (parseInt(donation.amount) || 0), 0) || (user?.totalDonations || 0),
    totalDonations: donations.length || (user?.donationsCount || 0),
    campaignsVolunteered: (() => {
      if (!user) return 0;
      const volunteeredKey = `volunteered_${user.email}`;
      const volunteered = localStorage.getItem(volunteeredKey);
      return volunteered ? JSON.parse(volunteered).length : 0;
    })(),
    lastDonation: donations.length > 0 
      ? new Date(donations[donations.length - 1].date || donations[donations.length - 1].createdAt).toLocaleDateString() 
      : 'Never'
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50">
        <div className="text-center">
          <div className="w-16 h-16 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading profile...</p>
        </div>
      </div>
    );
  }

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50">
        <div className="text-center">
          <FaUser className="text-5xl text-gray-300 mx-auto mb-4" />
          <h2 className="text-xl font-bold text-gray-700 mb-2">No User Found</h2>
          <p className="text-gray-500 mb-6">Please login to view your profile</p>
          <button
            onClick={() => navigate('/login')}
            className="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-xl hover:from-purple-700 hover:to-pink-600 transition"
          >
            Go to Login
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 p-4 md:p-6">
      {/* Header */}
      <div className="mb-8">
        <div className="bg-gradient-to-r from-purple-600 to-pink-500 rounded-3xl p-8 text-white shadow-xl">
          <div className="flex flex-col md:flex-row justify-between items-center">
            <div>
              <h1 className="text-3xl md:text-4xl font-bold mb-2">My Profile</h1>
              <p className="text-purple-100">Welcome back, {user.name}!</p>
            </div>
            <div className="mt-4 md:mt-0 flex items-center space-x-4">
              <button
                onClick={() => navigate('/dashboard')}
                className="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-xl hover:bg-white/30 transition flex items-center"
                title="Go to Dashboard"
              >
                <FaArrowLeft className="mr-2" />
                Dashboard
              </button>
              <button
                onClick={handleLogout}
                className="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-xl hover:bg-white/30 transition flex items-center"
                title="Logout"
              >
                <FaSignOutAlt className="mr-2" />
                Logout
              </button>
              <div className="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-3xl font-bold">
                {user.name.charAt(0).toUpperCase()}
              </div>
              <div className="text-right hidden md:block">
                <p className="text-sm text-purple-200">Role</p>
                <p className="text-lg font-bold">{user.role?.toUpperCase() || 'USER'}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Debug Buttons (Remove in production) */}
      <div className="mb-6 flex flex-wrap gap-2">
        <button
          onClick={addTestDonation}
          className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
        >
          Add Test Donation
        </button>
        <button
          onClick={clearAllDonations}
          className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm"
        >
          Clear Donations
        </button>
        <button
          onClick={loadUserData}
          className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm"
        >
          Refresh Data
        </button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Profile Information */}
        <div className="lg:col-span-2">
          <div className="bg-white rounded-2xl p-6 shadow-lg mb-8">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-bold text-gray-800">Personal Information</h2>
              {editing ? (
                <div className="flex gap-3">
                  <button
                    onClick={handleSaveProfile}
                    className="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition flex items-center"
                  >
                    <FaSave className="mr-2" />
                    Save
                  </button>
                  <button
                    onClick={() => setEditing(false)}
                    className="px-4 py-2 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition flex items-center"
                  >
                    <FaTimes className="mr-2" />
                    Cancel
                  </button>
                </div>
              ) : (
                <button
                  onClick={() => setEditing(true)}
                  className="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-xl hover:from-purple-700 hover:to-pink-600 transition flex items-center"
                >
                  <FaEdit className="mr-2" />
                  Edit Profile
                </button>
              )}
            </div>

            <div className="space-y-6">
              {/* Name */}
              <div className="flex items-start">
                <div className="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center mr-4">
                  <FaUser className="text-purple-600 text-xl" />
                </div>
                <div className="flex-1">
                  <label className="block text-gray-700 text-sm font-medium mb-1">Full Name</label>
                  {editing ? (
                    <input
                      type="text"
                      value={editForm.name}
                      onChange={(e) => setEditForm({...editForm, name: e.target.value})}
                      className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                      placeholder="Enter your name"
                    />
                  ) : (
                    <p className="text-lg font-medium text-gray-800">{user.name}</p>
                  )}
                </div>
              </div>

              {/* Email */}
              <div className="flex items-start">
                <div className="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                  <FaEnvelope className="text-blue-600 text-xl" />
                </div>
                <div className="flex-1">
                  <label className="block text-gray-700 text-sm font-medium mb-1">Email Address</label>
                  <p className="text-lg font-medium text-gray-800">{user.email}</p>
                </div>
              </div>

              {/* Mobile */}
              <div className="flex items-start">
                <div className="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                  <FaPhone className="text-green-600 text-xl" />
                </div>
                <div className="flex-1">
                  <label className="block text-gray-700 text-sm font-medium mb-1">Mobile Number</label>
                  {editing ? (
                    <input
                      type="tel"
                      value={editForm.mobile}
                      onChange={(e) => setEditForm({...editForm, mobile: e.target.value})}
                      className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                      placeholder="Enter mobile number"
                    />
                  ) : (
                    <p className="text-lg font-medium text-gray-800">{user.mobile}</p>
                  )}
                </div>
              </div>

              {/* Address */}
              <div className="flex items-start">
                <div className="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center mr-4">
                  <FaHome className="text-orange-600 text-xl" />
                </div>
                <div className="flex-1">
                  <label className="block text-gray-700 text-sm font-medium mb-1">Address</label>
                  {editing ? (
                    <textarea
                      value={editForm.address}
                      onChange={(e) => setEditForm({...editForm, address: e.target.value})}
                      className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                      rows="3"
                      placeholder="Enter your address"
                    />
                  ) : (
                    <p className="text-lg font-medium text-gray-800">{user.address}</p>
                  )}
                </div>
              </div>
            </div>
          </div>

          {/* Donation History */}
          <div className="bg-white rounded-2xl p-6 shadow-lg">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-bold text-gray-800">Donation History</h2>
              <div className="flex items-center space-x-4">
                <span className="text-purple-600 font-medium">
                  Total: ₹{stats.totalDonated.toLocaleString()}
                </span>
                <span className="text-gray-500">•</span>
                <span className="text-sm text-gray-500">
                  {stats.totalDonations} donation{stats.totalDonations !== 1 ? 's' : ''}
                </span>
              </div>
            </div>

            {donations.length === 0 ? (
              <div className="text-center py-12">
                <FaHeart className="text-5xl text-gray-300 mx-auto mb-4" />
                <h3 className="text-xl font-bold text-gray-500 mb-2">No donations yet</h3>
                <p className="text-gray-400 mb-6">Your donation history will appear here</p>
                <button
                  onClick={() => navigate('/campaigns')}
                  className="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-xl hover:from-purple-700 hover:to-pink-600 transition font-medium"
                >
                  Make Your First Donation
                </button>
              </div>
            ) : (
              <div className="space-y-4">
                {donations.slice().reverse().map((donation, index) => (
                  <div key={index} className="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                    <div className="flex items-center">
                      <div className="w-12 h-12 rounded-full bg-gradient-to-r from-green-400 to-blue-500 flex items-center justify-center mr-4">
                        <FaRupeeSign className="text-white text-xl" />
                      </div>
                      <div>
                        <p className="font-medium text-gray-800">{donation.campaignTitle || 'General Donation'}</p>
                        <p className="text-sm text-gray-500">
                          {donation.date ? new Date(donation.date).toLocaleDateString('en-IN', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                          }) : 'Recently'}
                        </p>
                      </div>
                    </div>
                    <div className="text-right">
                      <p className="text-xl font-bold text-green-600">₹{donation.amount || 0}</p>
                      <p className="text-sm text-gray-500">Completed</p>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Sidebar - Stats and Actions */}
        <div className="space-y-8">
          {/* Stats Card */}
          <div className="bg-white rounded-2xl p-6 shadow-lg">
            <h2 className="text-xl font-bold text-gray-800 mb-6">Your Impact</h2>
            <div className="space-y-6">
              <div className="p-4 bg-gradient-to-r from-green-50 to-blue-50 rounded-xl">
                <div className="flex items-center">
                  <div className="w-12 h-12 rounded-full bg-gradient-to-r from-green-400 to-blue-500 flex items-center justify-center mr-4">
                    <FaRupeeSign className="text-white text-xl" />
                  </div>
                  <div>
                    <p className="text-sm text-gray-600">Total Donated</p>
                    <p className="text-2xl font-bold text-gray-800">₹{stats.totalDonated.toLocaleString()}</p>
                  </div>
                </div>
              </div>

              <div className="p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl">
                <div className="flex items-center">
                  <div className="w-12 h-12 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center mr-4">
                    <FaHandHoldingHeart className="text-white text-xl" />
                  </div>
                  <div>
                    <p className="text-sm text-gray-600">Total Donations</p>
                    <p className="text-2xl font-bold text-gray-800">{stats.totalDonations}</p>
                  </div>
                </div>
              </div>

              <div className="p-4 bg-gradient-to-r from-orange-50 to-red-50 rounded-xl">
                <div className="flex items-center">
                  <div className="w-12 h-12 rounded-full bg-gradient-to-r from-orange-400 to-red-500 flex items-center justify-center mr-4">
                    <FaUsers className="text-white text-xl" />
                  </div>
                  <div>
                    <p className="text-sm text-gray-600">Campaigns Volunteered</p>
                    <p className="text-2xl font-bold text-gray-800">{stats.campaignsVolunteered}</p>
                  </div>
                </div>
              </div>

              <div className="p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl">
                <div className="flex items-center">
                  <div className="w-12 h-12 rounded-full bg-gradient-to-r from-blue-400 to-cyan-500 flex items-center justify-center mr-4">
                    <FaCalendarAlt className="text-white text-xl" />
                  </div>
                  <div>
                    <p className="text-sm text-gray-600">Last Donation</p>
                    <p className="text-2xl font-bold text-gray-800">{stats.lastDonation}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Account Actions */}
          <div className="bg-white rounded-2xl p-6 shadow-lg">
            <h2 className="text-xl font-bold text-gray-800 mb-6">Account Actions</h2>
            <div className="space-y-4">
              <button
                onClick={() => navigate('/dashboard')}
                className="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-xl hover:from-purple-700 hover:to-pink-600 transition flex items-center justify-center font-medium"
              >
                <FaChartLine className="mr-2" />
                View Dashboard
              </button>
              <button
                onClick={() => navigate('/campaigns')}
                className="w-full px-4 py-3 border-2 border-purple-600 text-purple-600 rounded-xl hover:bg-purple-50 transition flex items-center justify-center font-medium"
              >
                <FaHandHoldingHeart className="mr-2" />
                Donate Now
              </button>
              <button 
                onClick={handleDeleteAccount}
                className="w-full px-4 py-3 border-2 border-red-600 text-red-600 rounded-xl hover:bg-red-50 transition flex items-center justify-center font-medium"
              >
                <FaTrash className="mr-2" />
                Delete Account
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default UserProfile;