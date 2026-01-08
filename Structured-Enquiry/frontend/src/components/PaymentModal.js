import React, { useState } from 'react';
import { toast } from 'react-toastify';
import {
  FaCreditCard,
  FaRupeeSign,
  FaLock,
  FaShieldAlt,
  FaCheckCircle,
  FaTimes,
  FaMobileAlt,
  FaGlobe,
  FaWallet
} from 'react-icons/fa';

const PaymentModal = ({ isOpen, onClose, campaign, amount, setAmount, onSuccess }) => {
  const [paymentMethod, setPaymentMethod] = useState('card');
  const [loading, setLoading] = useState(false);

  const handlePayment = async () => {
    if (!amount || amount < 10) {
      toast.error('Minimum donation amount is ₹10');
      return;
    }

    if (amount > 100000) {
      toast.error('Maximum donation amount is ₹1,00,000');
      return;
    }

    setLoading(true);

    try {
      // Simulate payment processing
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      toast.success(`🎉 Donation of ₹${amount} successful! Thank you for your generosity!`);
      onSuccess(parseInt(amount));
      onClose();
    } catch (error) {
      toast.error('Payment failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen) return null;

  const presetAmounts = [100, 500, 1000, 2000, 5000];

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-3xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        {/* Header */}
        <div className="bg-gradient-to-r from-purple-600 to-pink-500 p-6 text-white rounded-t-3xl">
          <div className="flex justify-between items-center">
            <div className="flex items-center space-x-3">
              <FaRupeeSign className="text-2xl" />
              <div>
                <h2 className="text-xl font-bold">Support {campaign.title}</h2>
                <p className="text-purple-100">Make a difference today</p>
              </div>
            </div>
            <button onClick={onClose} className="text-white hover:text-gray-200">
              <FaTimes size={24} />
            </button>
          </div>
        </div>

        {/* Body */}
        <div className="p-6">
          {/* Campaign Progress */}
          <div className="mb-6 p-4 bg-purple-50 rounded-xl">
            <div className="flex justify-between items-center mb-2">
              <span className="text-sm text-gray-600">Campaign Progress</span>
              <span className="text-sm font-bold text-purple-600">
                {((campaign.currentAmount / campaign.targetAmount) * 100).toFixed(1)}%
              </span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2">
              <div 
                className="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full"
                style={{ width: `${(campaign.currentAmount / campaign.targetAmount) * 100}%` }}
              ></div>
            </div>
            <div className="flex justify-between text-xs text-gray-500 mt-2">
              <span>₹{campaign.currentAmount.toLocaleString()} raised</span>
              <span>Goal: ₹{campaign.targetAmount.toLocaleString()}</span>
            </div>
          </div>

          {/* Amount Selection */}
          <div className="mb-6">
            <label className="block text-gray-700 font-medium mb-3">Select Amount (₹)</label>
            <div className="grid grid-cols-3 gap-2 mb-4">
              {presetAmounts.map(preset => (
                <button
                  key={preset}
                  type="button"
                  onClick={() => setAmount(preset)}
                  className={`p-3 rounded-lg border ${amount === preset.toString() ? 'border-purple-500 bg-purple-50 text-purple-600' : 'border-gray-300 hover:border-purple-300'}`}
                >
                  ₹{preset}
                </button>
              ))}
              <button
                type="button"
                onClick={() => setAmount('')}
                className="p-3 rounded-lg border border-gray-300 hover:border-purple-300 col-span-3 text-gray-600"
              >
                Enter Custom Amount
              </button>
            </div>
            <div className="relative">
              <FaRupeeSign className="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" />
              <input
                type="number"
                value={amount}
                onChange={(e) => setAmount(e.target.value)}
                className="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none text-lg"
                placeholder="Enter amount"
                min="10"
                max="100000"
              />
            </div>
            <p className="text-sm text-gray-500 mt-2">Enter amount between ₹10 and ₹1,00,000</p>
          </div>

          {/* Payment Methods */}
          <div className="mb-6">
            <label className="block text-gray-700 font-medium mb-3">Payment Method</label>
            <div className="grid grid-cols-4 gap-3">
              {[
                { id: 'card', icon: FaCreditCard, label: 'Card' },
                { id: 'upi', icon: FaMobileAlt, label: 'UPI' },
                { id: 'netbanking', icon: FaGlobe, label: 'Bank' },
                { id: 'wallet', icon: FaWallet, label: 'Wallet' }
              ].map(method => (
                <button
                  key={method.id}
                  type="button"
                  onClick={() => setPaymentMethod(method.id)}
                  className={`p-3 rounded-xl border-2 flex flex-col items-center ${paymentMethod === method.id ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'}`}
                >
                  <method.icon className={`text-xl mb-2 ${paymentMethod === method.id ? 'text-purple-600' : 'text-gray-400'}`} />
                  <span className="text-sm">{method.label}</span>
                </button>
              ))}
            </div>
          </div>

          {/* Security Info */}
          <div className="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
            <div className="flex items-center space-x-3 mb-2">
              <FaLock className="text-green-600" />
              <span className="font-medium text-green-800">Secure Payment</span>
            </div>
            <div className="space-y-1 text-sm">
              <div className="flex items-center">
                <FaShieldAlt className="text-green-500 mr-2" />
                <span className="text-green-700">256-bit SSL encryption</span>
              </div>
              <div className="flex items-center">
                <FaCheckCircle className="text-green-500 mr-2" />
                <span className="text-green-700">PCI DSS compliant</span>
              </div>
            </div>
          </div>

          {/* Summary */}
          <div className="mb-6 p-4 bg-gray-50 rounded-xl">
            <div className="flex justify-between mb-2">
              <span className="text-gray-600">Donation Amount</span>
              <span className="font-bold">₹{amount || 0}</span>
            </div>
            <div className="border-t pt-2 flex justify-between">
              <span className="font-bold">Total Amount</span>
              <span className="text-2xl font-bold text-purple-600">₹{amount || 0}</span>
            </div>
          </div>

          {/* Action Buttons */}
          <div className="space-y-3">
            <button
              onClick={handlePayment}
              disabled={loading || !amount || amount < 10}
              className="w-full bg-gradient-to-r from-purple-600 to-pink-500 text-white font-semibold py-3 rounded-xl hover:from-purple-700 hover:to-pink-600 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              {loading ? (
                <>
                  <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                  Processing...
                </>
              ) : (
                <>
                  <FaRupeeSign className="mr-2" />
                  Donate ₹{amount}
                </>
              )}
            </button>
            <button
              onClick={onClose}
              className="w-full py-3 text-gray-600 hover:text-gray-800 font-medium"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PaymentModal;