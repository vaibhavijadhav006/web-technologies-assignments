import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import CampaignForm from './CampaignForm';
import {
  FaUsers,
  FaRupeeSign,
  FaHandHoldingHeart,
  FaChartLine,
  FaUser,
  FaEnvelope,
  FaCalendarAlt,
  FaSync,
  FaSignOutAlt,
  FaTrophy,
  FaFire,
  FaCheckCircle,
  FaEdit,
  FaTrash,
  FaPlus,
  FaTimes,
  FaSave,
  FaUserShield,
  FaBan,
  FaCheck
} from 'react-icons/fa';

const AdminDashboard = () => {
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState('dashboard'); // dashboard, campaigns, users
  const [stats, setStats] = useState({
    totalUsers: 0,
    totalCampaigns: 0,
    totalDonations: 0,
    activeCampaigns: 0,
    totalAmount: 0,
    averageDonation: 0
  });
  const [campaigns, setCampaigns] = useState([]);
  const [allUsers, setAllUsers] = useState([]);
  const [recentUsers, setRecentUsers] = useState([]);
  const [recentDonations, setRecentDonations] = useState([]);
  const [categoryStats, setCategoryStats] = useState([]);
  const [loading, setLoading] = useState(true);
  const [user, setUser] = useState(null);
  const [editingCampaign, setEditingCampaign] = useState(null);
  const [editCampaignForm, setEditCampaignForm] = useState({});
  const [showCreateCampaign, setShowCreateCampaign] = useState(false);

  useEffect(() => {
    loadUserData();
    if (activeTab === 'dashboard') {
      fetchDashboardData();
    } else if (activeTab === 'campaigns') {
      fetchCampaigns();
    } else if (activeTab === 'users') {
      fetchUsers();
    }
    
    // Set up real-time updates every 10 seconds for dashboard
    const interval = setInterval(() => {
      if (activeTab === 'dashboard') {
        fetchDashboardData();
      }
    }, 10000);

    return () => clearInterval(interval);
  }, [activeTab]);

  const loadUserData = () => {
    const userData = localStorage.getItem('user');
    if (userData) {
      const parsedUser = JSON.parse(userData);
      setUser(parsedUser);
      
      if (parsedUser.role !== 'admin') {
        toast.error('Admin access required');
        navigate('/dashboard');
      }
    } else {
      toast.error('Please login first');
      navigate('/login');
    }
  };

  const fetchDashboardData = async () => {
    try {
      setLoading(true);
      
      const response = await fetch('http://localhost:5000/api/admin/dashboard', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
      });

      if (response.ok) {
        const data = await response.json();
        
        if (data.success) {
          setStats(data.stats || {
            totalUsers: data.stats?.totalUsers || 0,
            totalCampaigns: data.stats?.totalCampaigns || 0,
            totalDonations: data.stats?.totalDonations || 0,
            activeCampaigns: data.stats?.activeCampaigns || 0,
            totalAmount: data.stats?.totalAmount || 0,
            averageDonation: data.stats?.averageDonation || data.stats?.avgDonation || 0
          });
          setRecentUsers(data.recentUsers || []);
          setRecentDonations(data.recentDonations || []);
          setCategoryStats(data.categoryStats || []);
        } else {
          loadLocalData();
        }
      } else {
        loadLocalData();
      }
    } catch (error) {
      console.error('Error fetching dashboard data:', error);
      loadLocalData();
    } finally {
      setLoading(false);
    }
  };

  const fetchCampaigns = async () => {
    try {
      setLoading(true);
      const response = await fetch('http://localhost:5000/api/campaigns', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.campaigns) {
          // Add volunteer counts - check if volunteers array exists or fetch separately
          const campaignsWithVolunteers = data.campaigns.map((campaign) => {
            // Count volunteers from localStorage if not in response
            let volunteersCount = campaign.volunteers?.length || 0;
            
            // Try to get from localStorage as fallback
            if (volunteersCount === 0) {
              const campaignId = campaign._id || campaign.id;
              // Count volunteers from localStorage
              let localVolunteerCount = 0;
              for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key && key.startsWith('volunteered_')) {
                  const email = key.replace('volunteered_', '');
                  const volunteered = JSON.parse(localStorage.getItem(key) || '[]');
                  if (Array.isArray(volunteered) && volunteered.includes(campaignId)) {
                    localVolunteerCount++;
                  }
                }
              }
              volunteersCount = localVolunteerCount;
            }
            
            return {
              ...campaign,
              _id: campaign._id || campaign.id,
              volunteersCount: volunteersCount
            };
          });
          setCampaigns(campaignsWithVolunteers);
        } else {
          // Fallback to localStorage
          const localCampaigns = JSON.parse(localStorage.getItem('campaigns') || '[]');
          const campaignsWithVolunteers = localCampaigns.map(campaign => {
            const campaignId = campaign.id || campaign._id;
            let volunteersCount = 0;
            for (let i = 0; i < localStorage.length; i++) {
              const key = localStorage.key(i);
              if (key && key.startsWith('volunteered_')) {
                const volunteered = JSON.parse(localStorage.getItem(key) || '[]');
                if (Array.isArray(volunteered) && volunteered.includes(campaignId)) {
                  volunteersCount++;
                }
              }
            }
            return { ...campaign, volunteersCount };
          });
          setCampaigns(campaignsWithVolunteers);
        }
      } else {
        const localCampaigns = JSON.parse(localStorage.getItem('campaigns') || '[]');
        const campaignsWithVolunteers = localCampaigns.map(campaign => {
          const campaignId = campaign.id || campaign._id;
          let volunteersCount = 0;
          for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith('volunteered_')) {
              const volunteered = JSON.parse(localStorage.getItem(key) || '[]');
              if (Array.isArray(volunteered) && volunteered.includes(campaignId)) {
                volunteersCount++;
              }
            }
          }
          return { ...campaign, volunteersCount };
        });
        setCampaigns(campaignsWithVolunteers);
      }
    } catch (error) {
      console.error('Error fetching campaigns:', error);
      const localCampaigns = JSON.parse(localStorage.getItem('campaigns') || '[]');
      setCampaigns(localCampaigns);
    } finally {
      setLoading(false);
    }
  };

  const fetchUsers = async () => {
    try {
      setLoading(true);
      const response = await fetch('http://localhost:5000/api/admin/users', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.users) {
          setAllUsers(data.users);
        } else {
          loadLocalUsers();
        }
      } else {
        loadLocalUsers();
      }
    } catch (error) {
      console.error('Error fetching users:', error);
      loadLocalUsers();
    } finally {
      setLoading(false);
    }
  };

  const loadLocalUsers = () => {
    const storedUsers = localStorage.getItem('allUsers');
    if (storedUsers) {
      try {
        const parsed = JSON.parse(storedUsers);
        setAllUsers(Array.isArray(parsed) ? parsed : []);
      } catch (e) {
        setAllUsers([]);
      }
    } else {
      setAllUsers([]);
    }
  };

  const loadLocalData = () => {
    const allUsers = [];
    const allDonations = [];
    const allCampaigns = JSON.parse(localStorage.getItem('campaigns') || '[]');
    
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key && key.startsWith('donations_')) {
        const email = key.replace('donations_', '');
        const donations = JSON.parse(localStorage.getItem(key) || '[]');
        donations.forEach(donation => {
          allDonations.push({
            ...donation,
            donorEmail: email
          });
        });
      }
    }

    const storedUsers = localStorage.getItem('allUsers');
    if (storedUsers) {
      try {
        const parsed = JSON.parse(storedUsers);
        if (Array.isArray(parsed)) {
          allUsers.push(...parsed);
        }
      } catch (e) {
        console.error('Error parsing stored users:', e);
      }
    }

    const totalAmount = allDonations.reduce((sum, d) => sum + (parseInt(d.amount) || 0), 0);
    const averageDonation = allDonations.length > 0 ? totalAmount / allDonations.length : 0;

    setStats({
      totalUsers: allUsers.length || 1,
      totalCampaigns: allCampaigns.length,
      totalDonations: allDonations.length,
      activeCampaigns: allCampaigns.filter(c => c.status !== 'completed').length,
      totalAmount: totalAmount,
      averageDonation: averageDonation
    });

    const sortedDonations = [...allDonations]
      .sort((a, b) => new Date(b.date || 0) - new Date(a.date || 0))
      .slice(0, 5)
      .map(d => ({
        donor: d.donorEmail || 'Unknown',
        campaign: d.campaignTitle || 'General Donation',
        amount: d.amount,
        date: d.date
      }));
    setRecentDonations(sortedDonations);
    setRecentUsers([]);
    
    const categoryMap = {};
    allCampaigns.forEach(campaign => {
      const category = campaign.category || 'Other';
      if (!categoryMap[category]) {
        categoryMap[category] = { count: 0, totalAmount: 0 };
      }
      categoryMap[category].count++;
      categoryMap[category].totalAmount += (campaign.currentAmount || 0);
    });
    
    const categoryArray = Object.entries(categoryMap).map(([category, data]) => ({
      _id: category,
      count: data.count,
      totalAmount: data.totalAmount
    }));
    setCategoryStats(categoryArray);
  };

  const handleCreateCampaign = () => {
    setShowCreateCampaign(true);
  };

  const handleCampaignCreated = () => {
    setShowCreateCampaign(false);
    setActiveTab('campaigns'); // Switch to campaigns tab to show the new campaign
    fetchCampaigns();
    fetchDashboardData();
  };

  const handleEditCampaign = (campaign) => {
    setEditingCampaign(campaign);
    setEditCampaignForm({
      title: campaign.title,
      description: campaign.description,
      category: campaign.category,
      targetAmount: campaign.targetAmount,
      endDate: campaign.endDate ? new Date(campaign.endDate).toISOString().split('T')[0] : '',
      status: campaign.status || 'active'
    });
  };

  const handleUpdateCampaign = async () => {
    if (!editingCampaign) return;

    try {
      // Get current user for auth
      const userData = localStorage.getItem('user');
      const user = userData ? JSON.parse(userData) : null;
      
      const campaignId = editingCampaign._id || editingCampaign.id;
      const response = await fetch(`http://localhost:5000/api/admin/campaigns/${campaignId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ...editCampaignForm,
          userId: user?.id || user?._id
        })
      });

      const data = await response.json();
      
      if (data.success) {
        toast.success('Campaign updated successfully!');
        setEditingCampaign(null);
        fetchCampaigns();
        fetchDashboardData();
      } else {
        // Fallback to localStorage
        const campaigns = JSON.parse(localStorage.getItem('campaigns') || '[]');
        const updatedCampaigns = campaigns.map(c => 
          (c.id === campaignId || c._id === campaignId) 
            ? { ...c, ...editCampaignForm }
            : c
        );
        localStorage.setItem('campaigns', JSON.stringify(updatedCampaigns));
        toast.success('Campaign updated (local)!');
        setEditingCampaign(null);
        fetchCampaigns();
      }
    } catch (error) {
      console.error('Error updating campaign:', error);
      toast.error('Failed to update campaign');
    }
  };

  const handleDeleteCampaign = async (campaignId) => {
    if (!window.confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) {
      return;
    }

    try {
      // Get current user for auth
      const userData = localStorage.getItem('user');
      const user = userData ? JSON.parse(userData) : null;
      
      const response = await fetch(`http://localhost:5000/api/admin/campaigns/${campaignId}`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          userId: user?.id || user?._id
        })
      });

      const data = await response.json();
      
      if (data.success) {
        toast.success('Campaign deleted successfully!');
        fetchCampaigns();
        fetchDashboardData();
      } else {
        // Fallback to localStorage
        const campaigns = JSON.parse(localStorage.getItem('campaigns') || '[]');
        const filtered = campaigns.filter(c => c.id !== campaignId && c._id !== campaignId);
        localStorage.setItem('campaigns', JSON.stringify(filtered));
        toast.success('Campaign deleted (local)!');
        fetchCampaigns();
      }
    } catch (error) {
      console.error('Error deleting campaign:', error);
      toast.error('Failed to delete campaign');
    }
  };

  const handleUpdateUser = async (userId, updates) => {
    try {
      const response = await fetch(`http://localhost:5000/api/admin/users/${userId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updates)
      });

      const data = await response.json();
      
      if (data.success) {
        toast.success('User updated successfully!');
        fetchUsers();
      } else {
        toast.error(data.message || 'Failed to update user');
      }
    } catch (error) {
      console.error('Error updating user:', error);
      toast.error('Failed to update user');
    }
  };

  const handleDeleteUser = async (userId) => {
    if (!window.confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
      return;
    }

    try {
      const response = await fetch(`http://localhost:5000/api/admin/users/${userId}`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' }
      });

      const data = await response.json();
      
      if (data.success) {
        toast.success('User deleted successfully!');
        fetchUsers();
        fetchDashboardData();
      } else {
        toast.error(data.message || 'Failed to delete user');
      }
    } catch (error) {
      console.error('Error deleting user:', error);
      toast.error('Failed to delete user');
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('user');
    localStorage.removeItem('token');
    toast.success('Logged out successfully');
    navigate('/login');
  };

  if (showCreateCampaign) {
    return <CampaignForm onSuccess={handleCampaignCreated} onCancel={() => setShowCreateCampaign(false)} />;
  }

  if (loading && activeTab === 'dashboard' && stats.totalUsers === 0) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50">
        <div className="text-center">
          <div className="w-16 h-16 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading dashboard...</p>
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
              <h1 className="text-3xl md:text-4xl font-bold mb-2">Admin Dashboard</h1>
              <p className="text-purple-100">Welcome back, {user?.name || 'Admin'}! Manage your platform here.</p>
            </div>
            <div className="mt-4 md:mt-0 flex items-center space-x-4">
              <button
                onClick={fetchDashboardData}
                className="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-xl hover:bg-white/30 transition flex items-center"
                title="Refresh Data"
              >
                <FaSync className="mr-2" />
                Refresh
              </button>
              <button
                onClick={handleLogout}
                className="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-xl hover:bg-white/30 transition flex items-center"
              >
                <FaSignOutAlt className="mr-2" />
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Tabs */}
      <div className="mb-6 bg-white rounded-2xl p-2 shadow-lg">
        <div className="flex flex-wrap gap-2">
          <button
            onClick={() => setActiveTab('dashboard')}
            className={`flex-1 md:flex-none px-6 py-3 rounded-xl font-semibold transition ${
              activeTab === 'dashboard'
                ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white'
                : 'text-gray-600 hover:bg-gray-100'
            }`}
          >
            <FaChartLine className="inline mr-2" />
            Dashboard
          </button>
          <button
            onClick={() => setActiveTab('campaigns')}
            className={`flex-1 md:flex-none px-6 py-3 rounded-xl font-semibold transition ${
              activeTab === 'campaigns'
                ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white'
                : 'text-gray-600 hover:bg-gray-100'
            }`}
          >
            <FaHandHoldingHeart className="inline mr-2" />
            Campaigns
          </button>
          <button
            onClick={() => setActiveTab('users')}
            className={`flex-1 md:flex-none px-6 py-3 rounded-xl font-semibold transition ${
              activeTab === 'users'
                ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white'
                : 'text-gray-600 hover:bg-gray-100'
            }`}
          >
            <FaUsers className="inline mr-2" />
            Users
          </button>
        </div>
      </div>

      {/* Dashboard Tab */}
      {activeTab === 'dashboard' && (
        <>
          {/* Stats Cards */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {[
              {
                title: 'Total Users',
                value: stats.totalUsers.toLocaleString(),
                icon: FaUsers,
                color: 'from-blue-400 to-cyan-500'
              },
              {
                title: 'Total Donated',
                value: `₹${stats.totalAmount.toLocaleString()}`,
                icon: FaRupeeSign,
                color: 'from-green-400 to-emerald-500'
              },
              {
                title: 'Total Campaigns',
                value: stats.totalCampaigns.toLocaleString(),
                icon: FaHandHoldingHeart,
                color: 'from-purple-500 to-pink-500'
              },
              {
                title: 'Total Donations',
                value: stats.totalDonations.toLocaleString(),
                icon: FaChartLine,
                color: 'from-orange-400 to-red-500'
              }
            ].map((stat, index) => (
              <div
                key={index}
                className="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300"
              >
                <div className="flex items-center justify-between mb-4">
                  <div className={`w-12 h-12 rounded-xl bg-gradient-to-br ${stat.color} flex items-center justify-center`}>
                    <stat.icon className="text-white text-xl" />
                  </div>
                </div>
                <h3 className="text-gray-600 text-sm mb-2">{stat.title}</h3>
                <span className="text-3xl font-bold text-gray-800">{stat.value}</span>
                {stat.title === 'Total Donated' && stats.averageDonation > 0 && (
                  <p className="text-xs text-gray-500 mt-2">
                    Avg: ₹{Math.round(stats.averageDonation).toLocaleString()}
                  </p>
                )}
              </div>
            ))}
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            {/* Recent Donations */}
            <div className="bg-white rounded-2xl p-6 shadow-lg">
              <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl font-bold text-gray-800">Recent Donations</h2>
                <FaFire className="text-orange-500" />
              </div>
              {recentDonations.length === 0 ? (
                <div className="text-center py-8">
                  <FaHandHoldingHeart className="text-4xl text-gray-300 mx-auto mb-3" />
                  <p className="text-gray-500">No donations yet</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {recentDonations.map((donation, index) => (
                    <div
                      key={index}
                      className="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition"
                    >
                      <div className="flex items-center">
                        <div className="w-12 h-12 rounded-full bg-gradient-to-r from-green-400 to-blue-500 flex items-center justify-center mr-4">
                          <FaRupeeSign className="text-white text-xl" />
                        </div>
                        <div>
                          <p className="font-medium text-gray-800">{donation.donor || 'Anonymous'}</p>
                          <p className="text-sm text-gray-500">{donation.campaign}</p>
                          {donation.date && (
                            <p className="text-xs text-gray-400 mt-1">
                              {new Date(donation.date).toLocaleDateString()}
                            </p>
                          )}
                        </div>
                      </div>
                      <div className="text-right">
                        <p className="text-xl font-bold text-green-600">₹{donation.amount || 0}</p>
                        <FaCheckCircle className="text-green-500 text-sm mx-auto mt-1" />
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Recent Users */}
            <div className="bg-white rounded-2xl p-6 shadow-lg">
              <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl font-bold text-gray-800">Recent Users</h2>
                <FaUsers className="text-blue-500" />
              </div>
              {recentUsers.length === 0 ? (
                <div className="text-center py-8">
                  <FaUser className="text-4xl text-gray-300 mx-auto mb-3" />
                  <p className="text-gray-500">No recent users</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {recentUsers.map((user, index) => (
                    <div
                      key={index}
                      className="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition"
                    >
                      <div className="flex items-center">
                        <div className="w-12 h-12 rounded-full bg-gradient-to-r from-purple-400 to-pink-500 flex items-center justify-center mr-4">
                          <FaUser className="text-white text-xl" />
                        </div>
                        <div>
                          <p className="font-medium text-gray-800">{user.name || 'User'}</p>
                          <p className="text-sm text-gray-500">{user.email || ''}</p>
                          {user.joined && (
                            <p className="text-xs text-gray-400 mt-1">
                              Joined: {new Date(user.joined).toLocaleDateString()}
                            </p>
                          )}
                        </div>
                      </div>
                      <div className="text-right">
                        <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
                          user.role === 'admin' 
                            ? 'bg-purple-100 text-purple-600' 
                            : 'bg-blue-100 text-blue-600'
                        }`}>
                          {user.role || 'user'}
                        </span>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>

          {/* Category Stats */}
          {categoryStats.length > 0 && (
            <div className="bg-white rounded-2xl p-6 shadow-lg mb-8">
              <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl font-bold text-gray-800">Campaigns by Category</h2>
                <FaTrophy className="text-yellow-500" />
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {categoryStats.map((category, index) => (
                  <div
                    key={index}
                    className="p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl border border-purple-100"
                  >
                    <h3 className="font-bold text-gray-800 mb-2">{category._id}</h3>
                    <div className="flex justify-between items-center">
                      <span className="text-sm text-gray-600">
                        {category.count} campaign{category.count !== 1 ? 's' : ''}
                      </span>
                      <span className="text-sm font-semibold text-purple-600">
                        ₹{category.totalAmount.toLocaleString()}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </>
      )}

      {/* Campaigns Tab */}
      {activeTab === 'campaigns' && (
        <div className="space-y-6">
          <div className="flex justify-between items-center">
            <h2 className="text-2xl font-bold text-gray-800">Manage Campaigns</h2>
            <button
              onClick={handleCreateCampaign}
              className="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-xl hover:from-purple-700 hover:to-pink-600 transition flex items-center"
            >
              <FaPlus className="mr-2" />
              Create Campaign
            </button>
          </div>

          {loading ? (
            <div className="text-center py-12">
              <div className="w-16 h-16 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
              <p className="mt-4 text-gray-600">Loading campaigns...</p>
            </div>
          ) : (
            <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead className="bg-gradient-to-r from-purple-600 to-pink-500 text-white">
                    <tr>
                      <th className="px-6 py-4 text-left">Title</th>
                      <th className="px-6 py-4 text-left">Category</th>
                      <th className="px-6 py-4 text-left">Target</th>
                      <th className="px-6 py-4 text-left">Current</th>
                      <th className="px-6 py-4 text-left">Volunteers</th>
                      <th className="px-6 py-4 text-left">Status</th>
                      <th className="px-6 py-4 text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {campaigns.length === 0 ? (
                      <tr>
                        <td colSpan="7" className="px-6 py-12 text-center text-gray-500">
                          No campaigns found. Create your first campaign!
                        </td>
                      </tr>
                    ) : (
                      campaigns.map((campaign) => (
                        <tr key={campaign._id || campaign.id} className="border-b hover:bg-gray-50">
                          <td className="px-6 py-4">
                            {editingCampaign && (editingCampaign._id === campaign._id || editingCampaign.id === campaign.id) ? (
                              <input
                                type="text"
                                value={editCampaignForm.title}
                                onChange={(e) => setEditCampaignForm({...editCampaignForm, title: e.target.value})}
                                className="w-full px-3 py-2 border rounded-lg"
                              />
                            ) : (
                              <div className="font-medium text-gray-800">{campaign.title}</div>
                            )}
                          </td>
                          <td className="px-6 py-4">
                            {editingCampaign && (editingCampaign._id === campaign._id || editingCampaign.id === campaign.id) ? (
                              <select
                                value={editCampaignForm.category}
                                onChange={(e) => setEditCampaignForm({...editCampaignForm, category: e.target.value})}
                                className="w-full px-3 py-2 border rounded-lg"
                              >
                                <option value="Education">Education</option>
                                <option value="Healthcare">Healthcare</option>
                                <option value="Disaster Relief">Disaster Relief</option>
                                <option value="Environment">Environment</option>
                                <option value="Animal Welfare">Animal Welfare</option>
                                <option value="Community Development">Community Development</option>
                                <option value="Children">Children</option>
                                <option value="Elderly">Elderly</option>
                                <option value="Other">Other</option>
                              </select>
                            ) : (
                              <span className="text-gray-600">{campaign.category}</span>
                            )}
                          </td>
                          <td className="px-6 py-4">
                            {editingCampaign && (editingCampaign._id === campaign._id || editingCampaign.id === campaign.id) ? (
                              <input
                                type="number"
                                value={editCampaignForm.targetAmount}
                                onChange={(e) => setEditCampaignForm({...editCampaignForm, targetAmount: e.target.value})}
                                className="w-full px-3 py-2 border rounded-lg"
                              />
                            ) : (
                              <span className="text-gray-600">₹{campaign.targetAmount?.toLocaleString() || 0}</span>
                            )}
                          </td>
                          <td className="px-6 py-4">
                            <span className="text-gray-600">₹{campaign.currentAmount?.toLocaleString() || 0}</span>
                          </td>
                          <td className="px-6 py-4">
                            <span className="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm font-semibold">
                              {campaign.volunteersCount || campaign.volunteers?.length || 0}
                            </span>
                          </td>
                          <td className="px-6 py-4">
                            {editingCampaign && (editingCampaign._id === campaign._id || editingCampaign.id === campaign.id) ? (
                              <select
                                value={editCampaignForm.status}
                                onChange={(e) => setEditCampaignForm({...editCampaignForm, status: e.target.value})}
                                className="w-full px-3 py-2 border rounded-lg"
                              >
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                              </select>
                            ) : (
                              <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
                                campaign.status === 'active' ? 'bg-green-100 text-green-600' :
                                campaign.status === 'completed' ? 'bg-blue-100 text-blue-600' :
                                'bg-red-100 text-red-600'
                              }`}>
                                {campaign.status || 'active'}
                              </span>
                            )}
                          </td>
                          <td className="px-6 py-4">
                            <div className="flex items-center justify-center gap-2">
                              {editingCampaign && (editingCampaign._id === campaign._id || editingCampaign.id === campaign.id) ? (
                                <>
                                  <button
                                    onClick={handleUpdateCampaign}
                                    className="p-2 bg-green-500 text-white rounded-lg hover:bg-green-600"
                                  >
                                    <FaSave />
                                  </button>
                                  <button
                                    onClick={() => setEditingCampaign(null)}
                                    className="p-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
                                  >
                                    <FaTimes />
                                  </button>
                                </>
                              ) : (
                                <>
                                  <button
                                    onClick={() => handleEditCampaign(campaign)}
                                    className="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                                  >
                                    <FaEdit />
                                  </button>
                                  <button
                                    onClick={() => handleDeleteCampaign(campaign._id || campaign.id)}
                                    className="p-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                                  >
                                    <FaTrash />
                                  </button>
                                </>
                              )}
                            </div>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      )}

      {/* Users Tab */}
      {activeTab === 'users' && (
        <div className="space-y-6">
          <div className="flex justify-between items-center">
            <h2 className="text-2xl font-bold text-gray-800">Manage Users</h2>
            <button
              onClick={fetchUsers}
              className="px-6 py-3 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition flex items-center"
            >
              <FaSync className="mr-2" />
              Refresh
            </button>
          </div>

          {loading ? (
            <div className="text-center py-12">
              <div className="w-16 h-16 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
              <p className="mt-4 text-gray-600">Loading users...</p>
            </div>
          ) : (
            <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead className="bg-gradient-to-r from-purple-600 to-pink-500 text-white">
                    <tr>
                      <th className="px-6 py-4 text-left">Name</th>
                      <th className="px-6 py-4 text-left">Email</th>
                      <th className="px-6 py-4 text-left">Role</th>
                      <th className="px-6 py-4 text-left">Status</th>
                      <th className="px-6 py-4 text-left">Total Donations</th>
                      <th className="px-6 py-4 text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {allUsers.length === 0 ? (
                      <tr>
                        <td colSpan="6" className="px-6 py-12 text-center text-gray-500">
                          No users found.
                        </td>
                      </tr>
                    ) : (
                      allUsers.map((u) => (
                        <tr key={u._id || u.id} className="border-b hover:bg-gray-50">
                          <td className="px-6 py-4 font-medium text-gray-800">{u.name}</td>
                          <td className="px-6 py-4 text-gray-600">{u.email}</td>
                          <td className="px-6 py-4">
                            <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
                              u.role === 'admin' 
                                ? 'bg-purple-100 text-purple-600' 
                                : 'bg-blue-100 text-blue-600'
                            }`}>
                              {u.role || 'user'}
                            </span>
                          </td>
                          <td className="px-6 py-4">
                            <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
                              u.isActive !== false 
                                ? 'bg-green-100 text-green-600' 
                                : 'bg-red-100 text-red-600'
                            }`}>
                              {u.isActive !== false ? 'Active' : 'Inactive'}
                            </span>
                          </td>
                          <td className="px-6 py-4 text-gray-600">
                            ₹{u.totalDonations?.toLocaleString() || 0}
                          </td>
                          <td className="px-6 py-4">
                            <div className="flex items-center justify-center gap-2">
                              <button
                                onClick={() => handleUpdateUser(u._id || u.id, { isActive: u.isActive !== false ? false : true })}
                                className={`p-2 rounded-lg ${
                                  u.isActive !== false
                                    ? 'bg-red-500 text-white hover:bg-red-600'
                                    : 'bg-green-500 text-white hover:bg-green-600'
                                }`}
                                title={u.isActive !== false ? 'Deactivate' : 'Activate'}
                              >
                                {u.isActive !== false ? <FaBan /> : <FaCheck />}
                              </button>
                              {u.role !== 'admin' && (
                                <button
                                  onClick={() => handleUpdateUser(u._id || u.id, { role: 'admin' })}
                                  className="p-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600"
                                  title="Make Admin"
                                >
                                  <FaUserShield />
                                </button>
                              )}
                              <button
                                onClick={() => handleDeleteUser(u._id || u.id)}
                                className="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                                title="Delete User"
                              >
                                <FaTrash />
                              </button>
                            </div>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default AdminDashboard;
