import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import PaymentModal from './PaymentModal';
import {
  FaSearch,
  FaFilter,
  FaRupeeSign,
  FaUsers,
  FaCalendarAlt,
  FaArrowRight,
  FaHeart,
  FaDonate,
  FaArrowLeft
} from 'react-icons/fa';

const CampaignList = () => {
  const [campaigns, setCampaigns] = useState([]);
  const [filteredCampaigns, setFilteredCampaigns] = useState([]);
  const [categories, setCategories] = useState(['all']);
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [searchTerm, setSearchTerm] = useState('');
  const [showPaymentModal, setShowPaymentModal] = useState(false);
  const [selectedCampaign, setSelectedCampaign] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    fetchCampaigns();
  }, []);

  useEffect(() => {
    filterCampaigns();
  }, [campaigns, selectedCategory, searchTerm]);

  const fetchCampaigns = async () => {
    try {
      setLoading(true);
      // Try to fetch from backend
      const response = await fetch('http://localhost:5000/api/campaigns', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.campaigns) {
          setCampaigns(data.campaigns);
          setFilteredCampaigns(data.campaigns);
          // Extract unique categories
          const uniqueCategories = ['all', ...new Set(data.campaigns.map(c => c.category))];
          setCategories(uniqueCategories);
        } else {
          loadFromLocalStorage();
        }
      } else {
        loadFromLocalStorage();
      }
    } catch (error) {
      console.error('Error fetching campaigns:', error);
      loadFromLocalStorage();
    } finally {
      setLoading(false);
    }
  };

  const loadFromLocalStorage = () => {
    const savedCampaigns = JSON.parse(localStorage.getItem('campaigns') || '[]');
    if (savedCampaigns.length > 0) {
      setCampaigns(savedCampaigns);
      setFilteredCampaigns(savedCampaigns);
      const uniqueCategories = ['all', ...new Set(savedCampaigns.map(c => c.category))];
      setCategories(uniqueCategories);
    } else {
      toast.info('No campaigns found.');
    }
  };

  const filterCampaigns = () => {
    let filtered = campaigns;
    
    // Filter by category
    if (selectedCategory !== 'all') {
      filtered = filtered.filter(campaign => campaign.category === selectedCategory);
    }
    
    // Filter by search term
    if (searchTerm) {
      filtered = filtered.filter(campaign =>
        campaign.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
        campaign.description.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }
    
    setFilteredCampaigns(filtered);
  };

  const handleDonate = (campaign) => {
    setSelectedCampaign(campaign);
    setShowPaymentModal(true);
  };

  const handlePaymentSuccess = async (amount) => {
    if (!selectedCampaign) return;
    
    try {
      const result = await campaignAPI.donate(selectedCampaign._id, {
        amount,
        paymentMethod: 'card'
      });
      
      if (result.success) {
        toast.success(`🎉 Donation of ₹${amount} successful!`);
        fetchCampaigns(); // Refresh campaigns
        
        // Update user data
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        if (user) {
          user.totalDonations = (user.totalDonations || 0) + amount;
          localStorage.setItem('user', JSON.stringify(user));
          window.dispatchEvent(new Event('storage'));
        }
      }
    } catch (error) {
      toast.error(error.response?.data?.message || 'Donation failed');
    }
  };

  const handleVolunteer = async (campaignId) => {
    try {
      const result = await campaignAPI.volunteer(campaignId);
      if (result.success) {
        toast.success('You are now a volunteer!');
      }
    } catch (error) {
      toast.error(error.response?.data?.message || 'Failed to volunteer');
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50">
        <div className="text-center">
          <div className="w-20 h-20 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading campaigns...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 p-4 md:p-6">
      {/* Payment Modal */}
      {selectedCampaign && (
        <PaymentModal
          isOpen={showPaymentModal}
          onClose={() => setShowPaymentModal(false)}
          campaign={selectedCampaign}
          onSuccess={handlePaymentSuccess}
        />
      )}

      {/* Header */}
      <div className="mb-8">
        <div className="bg-gradient-to-r from-purple-600 to-pink-500 rounded-3xl p-8 text-white shadow-xl">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl md:text-4xl font-bold mb-2">All Campaigns</h1>
              <p className="text-purple-100">Discover and support meaningful causes</p>
            </div>
            <button
              onClick={() => navigate('/dashboard')}
              className="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-xl hover:bg-white/30 transition flex items-center"
            >
              <FaArrowLeft className="mr-2" />
              Back to Dashboard
            </button>
          </div>
        </div>
      </div>

      {/* Filters */}
      <div className="mb-8 bg-white rounded-2xl p-6 shadow-lg">
        <div className="flex flex-col md:flex-row gap-4 items-center">
          <div className="flex-1 relative">
            <FaSearch className="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" />
            <input
              type="text"
              placeholder="Search campaigns..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
            />
          </div>
          
          <div className="flex gap-4 w-full md:w-auto">
            <select
              value={selectedCategory}
              onChange={(e) => setSelectedCategory(e.target.value)}
              className="flex-1 md:w-48 px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
            >
              {categories.map(category => (
                <option key={category} value={category}>
                  {category === 'all' ? 'All Categories' : category}
                </option>
              ))}
            </select>
            
            <button 
              onClick={() => {
                setSelectedCategory('all');
                setSearchTerm('');
              }}
              className="px-6 py-3 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      {/* Campaigns Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {filteredCampaigns.map(campaign => (
          <div 
            key={campaign._id}
            className="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300"
          >
            {/* Campaign Image */}
            <div className="h-48 bg-gradient-to-r from-blue-400 to-purple-500 relative">
              <div className="absolute inset-0 flex items-center justify-center">
                <FaHeart className="text-white text-4xl opacity-50" />
              </div>
              <div className="absolute top-4 left-4">
                <span className="px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-semibold text-purple-600">
                  {campaign.category}
                </span>
              </div>
            </div>

            {/* Campaign Content */}
            <div className="p-6">
              <h3 className="text-xl font-bold text-gray-800 mb-2">{campaign.title}</h3>
              <p className="text-gray-600 text-sm mb-4 line-clamp-3">
                {campaign.description}
              </p>

              {/* Progress Bar */}
              <div className="mb-4">
                <div className="flex justify-between text-sm text-gray-600 mb-1">
                  <span>Progress</span>
                  <span>{((campaign.currentAmount / campaign.targetAmount) * 100).toFixed(1)}%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full"
                    style={{ width: `${Math.min((campaign.currentAmount / campaign.targetAmount) * 100, 100)}%` }}
                  ></div>
                </div>
                <div className="flex justify-between text-xs text-gray-500 mt-1">
                  <span>₹{campaign.currentAmount?.toLocaleString() || 0}</span>
                  <span>₹{campaign.targetAmount?.toLocaleString() || 0}</span>
                </div>
              </div>

              {/* Campaign Stats */}
              <div className="flex justify-between text-sm text-gray-600 mb-6">
                <div className="flex items-center">
                  <FaUsers className="mr-2" />
                  <span>{campaign.donorsCount || 0} donors</span>
                </div>
                <div className="flex items-center">
                  <FaCalendarAlt className="mr-2" />
                  <span>{campaign.status}</span>
                </div>
              </div>

              {/* Actions */}
              <div className="flex gap-3">
                <button
                  onClick={() => handleDonate(campaign)}
                  className="flex-1 bg-gradient-to-r from-purple-600 to-pink-500 text-white font-semibold py-2 px-4 rounded-xl hover:from-purple-700 hover:to-pink-600 transition flex items-center justify-center"
                >
                  <FaDonate className="mr-2" />
                  Donate Now
                </button>
                <button
                  onClick={() => handleVolunteer(campaign._id)}
                  className="px-4 py-2 border border-purple-600 text-purple-600 rounded-xl hover:bg-purple-50 transition"
                >
                  Volunteer
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>

      {filteredCampaigns.length === 0 && (
        <div className="text-center py-12">
          <FaHeart className="text-5xl text-gray-300 mx-auto mb-4" />
          <h3 className="text-xl font-bold text-gray-500 mb-2">No campaigns found</h3>
          <p className="text-gray-400">Try adjusting your filters</p>
        </div>
      )}
    </div>
  );
};

export default CampaignList;