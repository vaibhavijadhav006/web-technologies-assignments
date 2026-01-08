import { toast } from 'react-toastify';

// Load Razorpay script dynamically
export const loadRazorpayScript = () => {
  return new Promise((resolve, reject) => {
    // Check if already loaded
    if (window.Razorpay) {
      resolve(window.Razorpay);
      return;
    }

    const script = document.createElement('script');
    script.src = 'https://checkout.razorpay.com/v1/checkout.js';
    script.async = true;
    
    script.onload = () => {
      if (window.Razorpay) {
        resolve(window.Razorpay);
      } else {
        reject(new Error('Razorpay SDK failed to load'));
      }
    };
    
    script.onerror = () => {
      reject(new Error('Failed to load Razorpay SDK. Please check your internet connection.'));
    };
    
    document.body.appendChild(script);
  });
};

// Initialize Razorpay payment
export const initializeRazorpayPayment = async (options) => {
  try {
    const Razorpay = await loadRazorpayScript();
    
    const rzpOptions = {
      key: options.key,
      amount: options.amount, // amount in paise
      currency: options.currency || 'INR',
      name: options.name || 'Donation Management System',
      description: options.description || 'Donation',
      image: options.image || 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
      order_id: options.order_id,
      handler: async (response) => {
        if (typeof options.handler === 'function') {
          options.handler(response);
        }
      },
      prefill: {
        name: options.prefill?.name || '',
        email: options.prefill?.email || '',
        contact: options.prefill?.contact || ''
      },
      notes: options.notes || {},
      theme: {
        color: options.theme?.color || '#667eea'
      },
      modal: {
        ondismiss: () => {
          if (typeof options.onDismiss === 'function') {
            options.onDismiss();
          }
          toast.info('Payment cancelled by user');
        },
        escape: true,
        backdropclose: true
      },
      retry: {
        enabled: true,
        max_count: 3
      },
      timeout: 300, // 5 minutes
      readonly: {
        contact: false,
        email: false,
        name: false
      }
    };

    const rzp = new Razorpay(rzpOptions);
    
    // Add event listeners
    rzp.on('payment.failed', (response) => {
      console.error('Payment failed:', response.error);
      if (typeof options.onFailure === 'function') {
        options.onFailure(response.error);
      }
      toast.error(`Payment failed: ${response.error.description}`);
    });

    rzp.on('payment.success', (response) => {
      console.log('Payment success:', response);
    });

    rzp.on('payment.authorized', (response) => {
      console.log('Payment authorized:', response);
    });

    // Open payment modal
    rzp.open();
    
    return rzp;
    
  } catch (error) {
    console.error('Razorpay initialization error:', error);
    toast.error(error.message || 'Failed to initialize payment');
    throw error;
  }
};

// Test payment function for development
export const testPayment = async (amount = 100) => {
  try {
    const Razorpay = await loadRazorpayScript();
    
    const options = {
      key: 'rzp_test_1DP5mmOlF5G5ag', // Test key from Razorpay
      amount: amount * 100,
      currency: 'INR',
      name: 'Test Payment',
      description: 'Test Transaction',
      handler: function(response) {
        alert(`Payment successful! Payment ID: ${response.razorpay_payment_id}`);
      },
      prefill: {
        name: 'Test User',
        email: 'test@example.com',
        contact: '9999999999'
      },
      theme: {
        color: '#667eea'
      }
    };

    const rzp = new Razorpay(options);
    rzp.open();
    
  } catch (error) {
    console.error('Test payment error:', error);
    toast.error('Test payment failed');
  }
};

// Utility functions for payment
export const paymentUtils = {
  // Convert amount to paise
  toPaise: (amount) => {
    return Math.round(amount * 100);
  },
  
  // Convert paise to rupees
  toRupees: (paise) => {
    return paise / 100;
  },
  
  // Format currency for display
  formatAmount: (amount) => {
    return `₹${amount.toLocaleString('en-IN')}`;
  },
  
  // Validate payment details
  validatePayment: (paymentData) => {
    const errors = [];
    
    if (!paymentData.amount || paymentData.amount < 10) {
      errors.push('Amount must be at least ₹10');
    }
    
    if (!paymentData.order_id) {
      errors.push('Order ID is required');
    }
    
    if (!paymentData.payment_id) {
      errors.push('Payment ID is required');
    }
    
    if (!paymentData.signature) {
      errors.push('Signature is required');
    }
    
    return errors;
  },
  
  // Get payment method icon
  getPaymentMethodIcon: (method) => {
    const icons = {
      card: '💳',
      upi: '📱',
      netbanking: '🏦',
      wallet: '👛',
      cash: '💰'
    };
    return icons[method] || '💸';
  },
  
  // Get payment status color
  getPaymentStatusColor: (status) => {
    const colors = {
      created: 'blue',
      attempted: 'orange',
      paid: 'green',
      failed: 'red',
      refunded: 'gray'
    };
    return colors[status] || 'gray';
  }
};

export default {
  loadRazorpayScript,
  initializeRazorpayPayment,
  testPayment,
  paymentUtils
};