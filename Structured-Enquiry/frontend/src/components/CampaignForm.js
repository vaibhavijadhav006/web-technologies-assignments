import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import {
  FaSave,
  FaTimes,
  FaUpload,
  FaCalendarAlt,
  FaRupeeSign,
  FaAlignLeft
} from 'react-icons/fa';

const CampaignForm = ({ onSuccess, onCancel }) => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    category: 'Education',
    targetAmount: '',
    endDate: '',
    imageUrl: ''
  });

  const categories = [
    'Education',
    'Healthcare',
    'Disaster Relief',
    'Environment',
    'Animal Welfare',
    'Community Development',
    'Children',
    'Elderly',
    'Other'
  ];

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!formData.title || !formData.description || !formData.targetAmount || !formData.endDate) {
      toast.error('Please fill all required fields');
      return;
    }

    setLoading(true);

    try {
      // Get current user
      const userData = localStorage.getItem('user');
      const user = userData ? JSON.parse(userData) : null;
      
      // Prepare request body
      const requestBody = {
        title: formData.title,
        description: formData.description,
        category: formData.category,
        targetAmount: parseInt(formData.targetAmount),
        endDate: formData.endDate
      };
      
      // Add userId for auth middleware
      if (user?.id) {
        requestBody.userId = user.id;
        requestBody.createdBy = user.id;
      } else if (user?._id) {
        requestBody.userId = user._id;
        requestBody.createdBy = user._id;
      }
      
      // Try to save to database
      const response = await fetch('http://localhost:5000/api/admin/campaigns', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestBody)
      });

      const data = await response.json();

      if (data.success) {
        toast.success('Campaign created successfully!');
        
        // Also save to localStorage as fallback
        const campaigns = JSON.parse(localStorage.getItem('campaigns') || '[]');
        const newCampaign = {
          id: data.campaign?.id || Date.now(),
          ...formData,
          currentAmount: 0,
          donors: 0,
          createdBy: user?.name || 'Admin',
          status: 'active',
          startDate: new Date().toISOString(),
          createdAt: new Date().toISOString()
        };
        campaigns.push(newCampaign);
        localStorage.setItem('campaigns', JSON.stringify(campaigns));

        if (onSuccess) {
          onSuccess();
        } else {
          navigate('/admin/dashboard');
        }
      } else {
        // Fallback to localStorage
        const campaigns = JSON.parse(localStorage.getItem('campaigns') || '[]');
        const newCampaign = {
          id: Date.now(),
          ...formData,
          currentAmount: 0,
          donors: 0,
          createdBy: user?.name || 'Admin',
          status: 'active',
          startDate: new Date().toISOString(),
          createdAt: new Date().toISOString()
        };
        campaigns.push(newCampaign);
        localStorage.setItem('campaigns', JSON.stringify(campaigns));
        
        toast.success('Campaign created (saved locally)!');
        if (onSuccess) {
          onSuccess();
        } else {
          navigate('/admin/dashboard');
        }
      }
    } catch (error) {
      console.error('Error creating campaign:', error);
      // Fallback to localStorage
      const campaigns = JSON.parse(localStorage.getItem('campaigns') || '[]');
      const userData = localStorage.getItem('user');
      const user = userData ? JSON.parse(userData) : null;
      const newCampaign = {
        id: Date.now(),
        ...formData,
        currentAmount: 0,
        donors: 0,
        createdBy: user?.name || 'Admin',
        status: 'active',
        startDate: new Date().toISOString(),
        createdAt: new Date().toISOString()
      };
      campaigns.push(newCampaign);
      localStorage.setItem('campaigns', JSON.stringify(campaigns));
      
      toast.success('Campaign created (saved locally)!');
      if (onSuccess) {
        onSuccess();
      } else {
        navigate('/admin/dashboard');
      }
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 p-4 md:p-6">
      <div className="max-w-4xl mx-auto">
        {/* Header */}
        <div className="mb-8">
          <div className="bg-gradient-to-r from-purple-600 to-pink-500 rounded-3xl p-8 text-white shadow-xl">
            <h1 className="text-3xl md:text-4xl font-bold mb-2">Create New Campaign</h1>
            <p className="text-purple-100">Start a new fundraising campaign</p>
          </div>
        </div>

        {/* Form */}
        <div className="bg-white rounded-2xl p-8 shadow-lg">
          <form onSubmit={handleSubmit}>
            <div className="space-y-6">
              {/* Title */}
              <div>
                <label className="block text-gray-700 font-medium mb-2">
                  Campaign Title *
                </label>
                <input
                  type="text"
                  name="title"
                  value={formData.title}
                  onChange={handleChange}
                  className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                  placeholder="Enter campaign title"
                  required
                />
              </div>

              {/* Description */}
              <div>
                <label className="block text-gray-700 font-medium mb-2">
                  <FaAlignLeft className="inline mr-2" />
                  Description *
                </label>
                <textarea
                  name="description"
                  value={formData.description}
                  onChange={handleChange}
                  className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                  rows="4"
                  placeholder="Describe your campaign in detail"
                  required
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* Category */}
                <div>
                  <label className="block text-gray-700 font-medium mb-2">
                    Category *
                  </label>
                  <select
                    name="category"
                    value={formData.category}
                    onChange={handleChange}
                    className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                    required
                  >
                    {categories.map(category => (
                      <option key={category} value={category}>
                        {category}
                      </option>
                    ))}
                  </select>
                </div>

                {/* Target Amount */}
                <div>
                  <label className="block text-gray-700 font-medium mb-2">
                    <FaRupeeSign className="inline mr-2" />
                    Target Amount (₹) *
                  </label>
                  <input
                    type="number"
                    name="targetAmount"
                    value={formData.targetAmount}
                    onChange={handleChange}
                    className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                    placeholder="Enter target amount"
                    min="100"
                    required
                  />
                </div>

                {/* End Date */}
                <div>
                  <label className="block text-gray-700 font-medium mb-2">
                    <FaCalendarAlt className="inline mr-2" />
                    End Date *
                  </label>
                  <input
                    type="date"
                    name="endDate"
                    value={formData.endDate}
                    onChange={handleChange}
                    className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                    required
                  />
                </div>

                {/* Image URL */}
                <div>
                  <label className="block text-gray-700 font-medium mb-2">
                    <FaUpload className="inline mr-2" />
                    Image URL (Optional)
                  </label>
                  <input
                    type="url"
                    name="imageUrl"
                    value={formData.imageUrl}
                    onChange={handleChange}
                    className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"
                    placeholder="https://example.com/image.jpg"
                  />
                </div>
              </div>
            </div>

            {/* Form Actions */}
            <div className="mt-8 flex gap-4">
              <button
                type="submit"
                disabled={loading}
                className="flex-1 bg-gradient-to-r from-purple-600 to-pink-500 text-white font-semibold py-3 rounded-xl hover:from-purple-700 hover:to-pink-600 transition disabled:opacity-50 flex items-center justify-center"
              >
                {loading ? (
                  <>
                    <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                    Creating...
                  </>
                ) : (
                  <>
                    <FaSave className="mr-2" />
                    Create Campaign
                  </>
                )}
              </button>
              <button
                type="button"
                onClick={() => {
                  if (onCancel) {
                    onCancel();
                  } else {
                    navigate('/admin/dashboard');
                  }
                }}
                className="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition"
              >
                <FaTimes className="mr-2 inline" />
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default CampaignForm;