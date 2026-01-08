import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { 
  FaHandHoldingHeart, 
  FaUsers, 
  FaRupeeSign, 
  FaChartLine,
  FaShieldAlt,
  FaHeartbeat,
  FaArrowRight,
  FaStar,
  FaCheckCircle,
  FaGlobe,
  FaHeart,
  FaRocket,
  FaAward,
  FaGift
} from 'react-icons/fa';

const LandingPage = () => {
  const navigate = useNavigate();
  const user = JSON.parse(localStorage.getItem('user') || 'null');

  const handleGetStarted = () => {
    if (user) {
      navigate('/dashboard');
    } else {
      navigate('/register');
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
      {/* Hero Section */}
      <div className="relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-r from-purple-600/10 to-pink-600/10"></div>
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="pt-20 pb-16 sm:pt-24 sm:pb-20 lg:pt-32 lg:pb-28">
            <div className="text-center">
              <div className="flex justify-center mb-6">
                <div className="w-24 h-24 bg-gradient-to-r from-purple-600 to-pink-500 rounded-3xl flex items-center justify-center shadow-2xl transform rotate-6 hover:rotate-0 transition-transform duration-300">
                  <FaHandHoldingHeart className="text-white text-5xl" />
                </div>
              </div>
              <h1 className="text-5xl sm:text-6xl md:text-7xl font-extrabold text-gray-900 mb-6">
                <span className="block">Transform Lives With</span>
                <span className="block text-transparent bg-clip-text bg-gradient-to-r from-purple-600 via-pink-500 to-red-500 animate-pulse">
                  Every Donation
                </span>
              </h1>
              <p className="mt-6 text-xl sm:text-2xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Join thousands of donors and volunteers making a <span className="font-semibold text-purple-600">real difference</span> in communities worldwide. 
                Your contribution today builds a better tomorrow.
              </p>
              <div className="mt-10 flex flex-col sm:flex-row gap-4 justify-center items-center">
                <button
                  onClick={handleGetStarted}
                  className="group px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-lg font-semibold rounded-2xl hover:from-purple-700 hover:to-pink-700 transform hover:scale-105 transition-all duration-300 shadow-xl hover:shadow-2xl flex items-center"
                >
                  <FaRocket className="mr-2 group-hover:translate-x-1 transition-transform" />
                  Get Started
                </button>
                {!user && (
                  <Link
                    to="/login"
                    className="px-8 py-4 border-2 border-purple-600 text-purple-600 text-lg font-semibold rounded-2xl hover:bg-purple-50 transform hover:scale-105 transition-all duration-300 flex items-center"
                  >
                    Sign In
                    <FaArrowRight className="ml-2" />
                  </Link>
                )}
              </div>
              
              {/* Trust Badges */}
              <div className="mt-12 flex flex-wrap justify-center gap-8 text-sm text-gray-600">
                <div className="flex items-center">
                  <FaShieldAlt className="text-green-500 mr-2" />
                  <span>Secure & Verified</span>
                </div>
                <div className="flex items-center">
                  <FaCheckCircle className="text-blue-500 mr-2" />
                  <span>100% Transparent</span>
                </div>
                <div className="flex items-center">
                  <FaHeart className="text-red-500 mr-2" />
                  <span>Trusted by 10K+ Donors</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Stats Section */}
      <div className="bg-gradient-to-r from-purple-600 via-pink-500 to-red-500 py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {[
              { value: '₹2.5M+', label: 'Raised', icon: FaRupeeSign, color: 'text-yellow-300' },
              { value: '500+', label: 'Campaigns', icon: FaHandHoldingHeart, color: 'text-pink-200' },
              { value: '10K+', label: 'Donors', icon: FaUsers, color: 'text-blue-200' },
              { value: '99.8%', label: 'Success Rate', icon: FaAward, color: 'text-green-200' }
            ].map((stat, index) => (
              <div key={index} className="text-center transform hover:scale-110 transition-transform duration-300">
                <div className={`inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm mb-4 ${stat.color}`}>
                  <stat.icon className="text-2xl" />
                </div>
                <div className="text-4xl font-extrabold text-white mb-2">{stat.value}</div>
                <div className="text-purple-100 font-medium">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Features Section */}
      <div className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-extrabold text-gray-900 mb-4">Why Choose Us?</h2>
            <p className="text-xl text-gray-600 max-w-2xl mx-auto">
              We provide a seamless, secure, and transparent platform for donors, volunteers, and administrators
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {[
              {
                icon: FaShieldAlt,
                title: 'Secure & Transparent',
                description: 'Every transaction is secured with bank-level encryption. Complete transparency in fund allocation.',
                color: 'from-blue-500 to-cyan-500'
              },
              {
                icon: FaHeartbeat,
                title: 'Verified Campaigns',
                description: 'All campaigns are thoroughly verified by our team before going live. Trust what you support.',
                color: 'from-red-500 to-pink-500'
              },
              {
                icon: FaChartLine,
                title: 'Impact Tracking',
                description: 'See exactly how your contributions are making a difference with real-time impact reports.',
                color: 'from-green-500 to-emerald-500'
              },
              {
                icon: FaGlobe,
                title: 'Global Reach',
                description: 'Support causes worldwide. From local communities to international relief efforts.',
                color: 'from-purple-500 to-indigo-500'
              },
              {
                icon: FaGift,
                title: 'Easy Donations',
                description: 'Simple, quick, and secure donation process. Multiple payment options available.',
                color: 'from-orange-500 to-red-500'
              },
              {
                icon: FaUsers,
                title: 'Community Driven',
                description: 'Join a community of changemakers. Volunteer, donate, and make a real impact together.',
                color: 'from-pink-500 to-rose-500'
              }
            ].map((feature, index) => (
              <div 
                key={index} 
                className="group bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100"
              >
                <div className={`w-16 h-16 rounded-2xl bg-gradient-to-r ${feature.color} flex items-center justify-center mb-6 transform group-hover:scale-110 transition-transform duration-300`}>
                  <feature.icon className="text-white text-2xl" />
                </div>
                <h3 className="text-2xl font-bold text-gray-900 mb-3">{feature.title}</h3>
                <p className="text-gray-600 leading-relaxed">{feature.description}</p>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* How It Works */}
      <div className="py-20 bg-gradient-to-br from-purple-50 to-blue-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-extrabold text-gray-900 mb-4">How It Works</h2>
            <p className="text-xl text-gray-600">Making a difference is just three steps away</p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {[
              { step: '1', title: 'Browse Campaigns', desc: 'Explore verified campaigns across various causes', icon: FaHandHoldingHeart },
              { step: '2', title: 'Donate or Volunteer', desc: 'Choose to donate funds or volunteer your time', icon: FaHeart },
              { step: '3', title: 'Track Impact', desc: 'See how your contribution is making a real difference', icon: FaChartLine }
            ].map((item, index) => (
              <div key={index} className="text-center">
                <div className="relative inline-block mb-6">
                  <div className="w-24 h-24 bg-gradient-to-r from-purple-600 to-pink-500 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-xl">
                    {item.step}
                  </div>
                  <div className="absolute -top-2 -right-2 w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg">
                    <item.icon className="text-purple-600 text-xl" />
                  </div>
                </div>
                <h3 className="text-2xl font-bold text-gray-900 mb-3">{item.title}</h3>
                <p className="text-gray-600">{item.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* CTA Section */}
      <div className="py-20 bg-white">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="bg-gradient-to-r from-purple-600 via-pink-500 to-red-500 rounded-3xl p-12 text-center shadow-2xl transform hover:scale-105 transition-transform duration-300">
            <div className="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full mb-6">
              <FaHandHoldingHeart className="text-white text-4xl" />
            </div>
            <h2 className="text-4xl font-extrabold text-white mb-4">
              Ready to Make a Difference?
            </h2>
            <p className="text-xl text-purple-100 mb-8 max-w-2xl mx-auto">
              Join our community of changemakers today. Every contribution counts, and together we can build a better world!
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <button
                onClick={handleGetStarted}
                className="px-8 py-4 bg-white text-purple-600 text-lg font-semibold rounded-2xl hover:bg-purple-50 transform hover:scale-105 transition-all duration-300 shadow-xl flex items-center justify-center"
              >
                <FaRocket className="mr-2" />
                Get Started Now
              </button>
              {!user && (
                <Link
                  to="/login"
                  className="px-8 py-4 bg-white/20 backdrop-blur-sm text-white text-lg font-semibold rounded-2xl hover:bg-white/30 transform hover:scale-105 transition-all duration-300 border-2 border-white flex items-center justify-center"
                >
                  Sign In
                  <FaArrowRight className="ml-2" />
                </Link>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Footer */}
      <div className="bg-gray-900 text-gray-300 py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <div className="flex justify-center mb-6">
            <FaHandHoldingHeart className="text-4xl text-pink-500" />
          </div>
          <p className="text-lg mb-4">Donation & Charity Management Platform</p>
          <p className="text-sm">Making the world a better place, one donation at a time.</p>
        </div>
      </div>
    </div>
  );
};

export default LandingPage;
