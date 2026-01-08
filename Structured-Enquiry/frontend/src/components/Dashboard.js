import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import PaymentModal from './PaymentModal';
import { 
  FaHeart, 
  FaRupeeSign, 
  FaUsers, 
  FaChartLine,
  FaSearch,
  FaFilter,
  FaArrowRight,
  FaDonate,
  FaHandHoldingHeart,
  FaTrophy,
  FaFire,
  FaStar,
  FaUser
} from 'react-icons/fa';

const Dashboard = () => {
  const navigate = useNavigate();
  const [searchTerm, setSearchTerm] = useState('');
  const [user, setUser] = useState(null);
  const [campaigns, setCampaigns] = useState([]);
  const [showPaymentModal, setShowPaymentModal] = useState(false);
  const [selectedCampaign, setSelectedCampaign] = useState(null);
  const [donationAmount, setDonationAmount] = useState('');
  
  useEffect(() => {
    loadUserData();
    loadCampaigns();
  }, []);

  const loadUserData = () => {
    const userData = localStorage.getItem('user');
    if (userData) {
      const parsedUser = JSON.parse(userData);
      const donations = JSON.parse(localStorage.getItem(`donations_${parsedUser.email}`) || '[]');
      const totalDonated = donations.reduce((sum, donation) => sum + (donation.amount || 0), 0);
      
      setUser({
        ...parsedUser,
        totalDonated,
        donationsCount: donations.length,
        campaignsVolunteered: JSON.parse(localStorage.getItem(`volunteered_${parsedUser.email}`) || '[]').length
      });
    }
  };

  const loadCampaigns = () => {
    const savedCampaigns = JSON.parse(localStorage.getItem('campaigns') || '[]');
    if (savedCampaigns.length === 0) {
      const defaultCampaigns = [
        {
          id: 1,
          title: 'Education for Underprivileged Children',
          category: 'Education',
          description: 'Help provide education to children from low-income families',
          currentAmount: 75000,
          targetAmount: 200000,
          donors: 45,
          imageUrl: ''
        },
        {
          id: 2,
          title: 'Healthcare for Rural Areas',
          category: 'Healthcare',
          description: 'Support medical camps in remote villages',
          currentAmount: 120000,
          targetAmount: 300000,
          donors: 68,
          imageUrl: ''
        },
        {
          id: 3,
          title: 'Disaster Relief Fund',
          category: 'Disaster Relief',
          description: 'Emergency aid for flood-affected families',
          currentAmount: 180000,
          targetAmount: 500000,
          donors: 92,
          imageUrl: ''
        },
        {
          id: 4,
          title: 'Animal Shelter Support',
          category: 'Animal Welfare',
          description: 'Food and care for abandoned animals',
          currentAmount: 45000,
          targetAmount: 100000,
          donors: 28,
          imageUrl: ''
        }
      ];
      setCampaigns(defaultCampaigns);
      localStorage.setItem('campaigns', JSON.stringify(defaultCampaigns));
    } else {
      setCampaigns(savedCampaigns);
    }
  };

  const handleDonateClick = (campaign) => {
    if (!user) {
      toast.error('Please login first');
      navigate('/login');
      return;
    }
    
    setSelectedCampaign(campaign);
    setDonationAmount('');
    setShowPaymentModal(true);
  };

  const handlePaymentSuccess = (amount) => {
    if (!selectedCampaign || !user) return;
    
    // Update campaign
    const updatedCampaigns = campaigns.map(campaign => {
      if (campaign.id === selectedCampaign.id) {
        return {
          ...campaign,
          currentAmount: campaign.currentAmount + amount,
          donors: (campaign.donors || 0) + 1
        };
      }
      return campaign;
    });
    
    setCampaigns(updatedCampaigns);
    localStorage.setItem('campaigns', JSON.stringify(updatedCampaigns));
    const handleDonate = async (campaignId, amount = 500) => {
    const user = JSON.parse(localStorage.getItem('user'));
    if (!user || !user.id) {
        toast.error('Please login first');
        navigate('/login');
        return;
    }

    try {
        // CORRECT API CALL
        const response = await fetch('http://localhost:5000/api/donate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                userId: user.id,
                campaignId: campaignId,
                amount: amount
            })
        });

        const data = await response.json();
        console.log('Donation response:', data);
        
        if (data.success) {
            toast.success(data.message);
            
            // Update local user data
            const updatedUser = { ...user, totalDonations: user.totalDonations + amount };
            localStorage.setItem('user', JSON.stringify(updatedUser));
            setUser(updatedUser);
            
            // Refresh campaigns
            fetchCampaigns();
        } else {
            toast.error(data.message || 'Donation failed');
        }
    } catch (error) {
        console.error('Donation error:', error);
        toast.error('Cannot connect to server');
    }
};

    // Save donation
    const donationsKey = `donations_${user.email}`;
    const donations = JSON.parse(localStorage.getItem(donationsKey) || '[]');
    
    donations.push({
      id: Date.now(),
      campaignId: selectedCampaign.id,
      campaignTitle: selectedCampaign.title,
      amount: amount,
      date: new Date().toISOString()
    });
    
    localStorage.setItem(donationsKey, JSON.stringify(donations));
    
    // Update user stats
    setUser(prev => ({
      ...prev,
      totalDonated: (prev.totalDonated || 0) + amount,
      donationsCount: (prev.donationsCount || 0) + 1
    }));

    toast.success(`Thank you for donating ₹${amount}!`);
    setShowPaymentModal(false);
  };

  const handleQuickDonate = (campaignId, amount) => {
    const campaign = campaigns.find(c => c.id === campaignId);
    if (campaign) {
      setSelectedCampaign(campaign);
      handlePaymentSuccess(amount);
    }
  };

  const handleVolunteer = (campaignId) => {
    if (!user) {
      toast.error('Please login first');
      navigate('/login');
      return;
    }

    const volunteeredKey = `volunteered_${user.email}`;
    const volunteered = JSON.parse(localStorage.getItem(volunteeredKey) || '[]');
    
    if (!volunteered.includes(campaignId)) {
      volunteered.push(campaignId);
      localStorage.setItem(volunteeredKey, JSON.stringify(volunteered));
      
      setUser(prev => ({
        ...prev,
        campaignsVolunteered: (prev.campaignsVolunteered || 0) + 1
      }));

      toast.success('You are now a volunteer for this campaign!');
    } else {
      toast.info('You are already a volunteer for this campaign');
    }
  };

  const filteredCampaigns = campaigns.filter(campaign =>
    campaign.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
    campaign.description.toLowerCase().includes(searchTerm.toLowerCase())
  );

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50">
        <div className="text-center">
          <div className="w-20 h-20 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading...</p>
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
          amount={donationAmount}
          setAmount={setDonationAmount}
          onSuccess={handlePaymentSuccess}
        />
      )}

      {/* Header */}
      <div className="mb-8">
        <div className="bg-gradient-to-r from-purple-600 to-pink-500 rounded-3xl p-8 text-white shadow-xl">
          <div className="flex flex-col md:flex-row justify-between items-center">
            <div>
              <h1 className="text-3xl md:text-4xl font-bold mb-2">Welcome back, {user.name}! 👋</h1>
              <p className="text-purple-100">
                "The best way to find yourself is to lose yourself in the service of others." — Mahatma Gandhi
              </p>
            </div>
            <div className="mt-4 md:mt-0 flex items-center gap-4">
              <button
                onClick={() => navigate('/profile')}
                className="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-xl hover:bg-white/30 transition flex items-center"
                title="View Profile"
              >
                <FaUser className="mr-2" />
                Profile
              </button>
              <div className="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-2xl cursor-pointer hover:bg-white/30 transition"
                   onClick={() => navigate('/profile')}
                   title="View Profile">
                {user.name.charAt(0)}
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {[
          {
            title: 'Total Donated',
            value: `₹${user.totalDonated.toLocaleString()}`,
            icon: FaRupeeSign,
            color: 'from-green-400 to-blue-500'
          },
          {
            title: 'Total Donations',
            value: user.donationsCount,
            icon: FaHeart,
            color: 'from-purple-500 to-pink-500'
          },
          {
            title: 'Volunteered',
            value: user.campaignsVolunteered,
            icon: FaUsers,
            color: 'from-orange-400 to-red-500'
          },
          {
            title: 'Impact Score',
            value: Math.round(user.totalDonated / 100 + user.donationsCount * 10 + user.campaignsVolunteered * 20),
            icon: FaChartLine,
            color: 'from-blue-400 to-cyan-500'
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
              <span className="text-2xl font-bold text-gray-800">{stat.value}</span>
            </div>
            <h3 className="text-gray-600 text-sm">{stat.title}</h3>
          </div>
        ))}
      </div>

      {/* Quick Donate Options */}
      <div className="mb-8 bg-white rounded-2xl p-6 shadow-lg">
        <h2 className="text-xl font-bold text-gray-800 mb-6">Quick Donate</h2>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          {[100, 500, 1000, 5000].map((amount) => (
            <button
              key={amount}
              onClick={() => campaigns.length > 0 && handleQuickDonate(campaigns[0].id, amount)}
              className="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 shadow hover:shadow-xl transition-all hover:scale-105 text-center border border-purple-100"
            >
              <div className="text-2xl font-bold text-purple-600 mb-2">₹{amount}</div>
              <div className="text-sm text-gray-600">Quick Donate</div>
            </button>
          ))}
        </div>
      </div>

      {/* Search and Filter */}
      <div className="mb-8">
        <div className="bg-white rounded-2xl p-6 shadow-lg">
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
          </div>
        </div>
      </div>

      {/* Campaigns Grid */}
      <div className="mb-8">
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-2xl font-bold text-gray-800">Featured Campaigns</h2>
          <button 
            onClick={() => navigate('/campaigns')}
            className="text-purple-600 hover:text-purple-800 font-medium flex items-center"
          >
            View All <FaArrowRight className="ml-2" />
          </button>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 gap-6">
          {filteredCampaigns.slice(0, 4).map((campaign) => (
            <div 
              key={campaign.id}
              className="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
            >
              {/* Campaign Image */}
              <div className="h-48 bg-gradient-to-r from-blue-400 to-purple-500 relative">
                <div className="absolute inset-0 flex items-center justify-center">
                  <FaHandHoldingHeart className="text-white text-4xl opacity-50" />
                </div>
                <div className="absolute top-4 left-4">
                  <span className="px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-semibold text-purple-600">
                    {campaign.category}
                  </span>
                </div>
              </div>

              {/* Campaign Content */}
              <div className="p-6">
                <h3 className="text-lg font-bold text-gray-800 mb-2 line-clamp-2">
                  {campaign.title}
                </h3>
                <p className="text-gray-600 text-sm mb-4 line-clamp-2">
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
                      className="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full transition-all duration-500"
                      style={{ width: `${Math.min((campaign.currentAmount / campaign.targetAmount) * 100, 100)}%` }}
                    ></div>
                  </div>
                  <div className="flex justify-between text-xs text-gray-500 mt-1">
                    <span>₹{campaign.currentAmount.toLocaleString()}</span>
                    <span>₹{campaign.targetAmount.toLocaleString()}</span>
                  </div>
                </div>

                {/* Actions */}
                <div className="flex gap-3">
                  <button
                    onClick={() => handleDonateClick(campaign)}
                    className="flex-1 bg-gradient-to-r from-purple-600 to-pink-500 text-white font-semibold py-2 px-4 rounded-xl hover:from-purple-700 hover:to-pink-600 transition flex items-center justify-center"
                  >
                    <FaDonate className="mr-2" />
                    Donate Now
                  </button>
                  <button
                    onClick={() => handleVolunteer(campaign.id)}
                    className="px-4 py-2 border border-purple-600 text-purple-600 rounded-xl hover:bg-purple-50 transition"
                  >
                    Volunteer
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* CTA */}
      <div className="bg-gradient-to-r from-purple-600 to-pink-500 rounded-2xl p-8 text-white text-center shadow-xl">
        <h2 className="text-2xl font-bold mb-4">Ready to Make More Impact?</h2>
        <p className="text-purple-100 mb-6 max-w-2xl mx-auto">
          Your next donation could provide education for a child, meals for a family, or medical care for someone in need.
        </p>
        <button
          onClick={() => navigate('/campaigns')}
          className="bg-white text-purple-600 font-semibold py-3 px-8 rounded-xl hover:bg-purple-50 transition inline-flex items-center"
        >
          <FaHeart className="mr-2" />
          Explore More Campaigns
        </button>
      </div>
    </div>
  );
};

export default Dashboard;