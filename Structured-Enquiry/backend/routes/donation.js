const express = require('express');
const router = express.Router();
const { auth } = require('../middleware/auth');
const Donation = require('../models/Donation');
const Campaign = require('../models/Campaign');
const User = require('../models/User');

// @route   POST /api/donations
// @desc    Create a donation
// @access  Private
router.post('/', auth, async (req, res) => {
  try {
    const { campaignId, amount, paymentMethod } = req.body;

    // Validate input
    if (!campaignId || !amount || amount < 10) {
      return res.status(400).json({
        success: false,
        message: 'Invalid donation data. Minimum amount is ₹10'
      });
    }

    // Check campaign exists
    const campaign = await Campaign.findById(campaignId);
    if (!campaign) {
      return res.status(404).json({
        success: false,
        message: 'Campaign not found'
      });
    }

    // Create donation record
    const donation = new Donation({
      user: req.userId,
      campaign: campaignId,
      amount,
      paymentMethod: paymentMethod || 'card',
      transactionId: 'TXN' + Date.now() + Math.random().toString(36).substr(2, 9),
      status: 'completed'
    });

    await donation.save();

    // Update campaign
    campaign.currentAmount += amount;
    campaign.donorsCount += 1;
    campaign.donations.push({
      user: req.userId,
      amount,
      date: new Date()
    });
    await campaign.save();

    // Update user
    await User.findByIdAndUpdate(req.userId, {
      $inc: { totalDonations: amount },
      $push: {
        donations: {
          campaign: campaignId,
          amount,
          date: new Date()
        }
      }
    });

    res.status(201).json({
      success: true,
      message: 'Donation successful!',
      donation: {
        id: donation._id,
        amount: donation.amount,
        transactionId: donation.transactionId,
        date: donation.createdAt,
        campaign: campaign.title
      }
    });

  } catch (error) {
    console.error('Donation error:', error);
    res.status(500).json({
      success: false,
      message: 'Server error during donation'
    });
  }
});

// @route   GET /api/donations/user
// @desc    Get user's donations
// @access  Private
router.get('/user', auth, async (req, res) => {
  try {
    const donations = await Donation.find({ user: req.userId })
      .populate('campaign', 'title category')
      .sort({ createdAt: -1 });

    res.json({
      success: true,
      donations
    });
  } catch (error) {
    console.error('Get donations error:', error);
    res.status(500).json({
      success: false,
      message: 'Server error'
    });
  }
});

module.exports = router;