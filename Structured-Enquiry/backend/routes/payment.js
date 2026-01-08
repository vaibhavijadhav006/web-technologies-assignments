const express = require('express');
const router = express.Router();
const Razorpay = require('razorpay');
const crypto = require('crypto');
const { auth } = require('../middleware/auth');
const Payment = require('../models/Payment');
const Campaign = require('../models/Campaign');
const User = require('../models/User');
const Donation = require('../models/Donation');

// Initialize Razorpay
const razorpay = new Razorpay({
    key_id: process.env.RAZORPAY_KEY_ID,
    key_secret: process.env.RAZORPAY_KEY_SECRET
});

// @route   POST /api/payment/create-order
// @desc    Create Razorpay order
// @access  Private
router.post('/create-order', auth, async (req, res) => {
    try {
        const { amount, campaignId } = req.body;

        // Validate amount
        if (!amount || amount < 10) {
            return res.status(400).json({
                success: false,
                message: 'Minimum donation amount is ₹10'
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

        // Create Razorpay order
        const options = {
            amount: amount * 100, // Convert to paise
            currency: 'INR',
            receipt: `receipt_${Date.now()}`,
            payment_capture: 1,
            notes: {
                userId: req.userId,
                campaignId: campaignId,
                campaignTitle: campaign.title
            }
        };

        const order = await razorpay.orders.create(options);

        // Save payment record
        const payment = new Payment({
            razorpay_order_id: order.id,
            user: req.userId,
            campaign: campaignId,
            amount: amount,
            status: 'created'
        });

        await payment.save();

        res.json({
            success: true,
            order: {
                id: order.id,
                amount: order.amount,
                currency: order.currency,
                key: process.env.RAZORPAY_KEY_ID
            },
            paymentId: payment._id
        });

    } catch (error) {
        console.error('Create order error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to create payment order'
        });
    }
});

// @route   POST /api/payment/verify
// @desc    Verify payment signature
// @access  Private
router.post('/verify', auth, async (req, res) => {
    try {
        const {
            razorpay_order_id,
            razorpay_payment_id,
            razorpay_signature,
            paymentId
        } = req.body;

        // Verify signature
        const body = razorpay_order_id + "|" + razorpay_payment_id;
        const expectedSignature = crypto
            .createHmac('sha256', process.env.RAZORPAY_KEY_SECRET)
            .update(body.toString())
            .digest('hex');

        if (expectedSignature === razorpay_signature) {
            // Update payment record
            const payment = await Payment.findById(paymentId);
            if (!payment) {
                return res.status(404).json({
                    success: false,
                    message: 'Payment record not found'
                });
            }

            payment.razorpay_payment_id = razorpay_payment_id;
            payment.razorpay_signature = razorpay_signature;
            payment.status = 'paid';
            payment.updatedAt = Date.now();
            await payment.save();

            // Update campaign
            const campaign = await Campaign.findById(payment.campaign);
            if (campaign) {
                campaign.currentAmount += payment.amount;
                campaign.donorsCount += 1;
                campaign.donations.push({
                    user: req.userId,
                    amount: payment.amount,
                    date: new Date()
                });
                await campaign.save();
            }

            // Update user
            await User.findByIdAndUpdate(req.userId, {
                $inc: { totalDonations: payment.amount },
                $push: {
                    donations: {
                        campaign: payment.campaign,
                        amount: payment.amount,
                        date: new Date()
                    }
                }
            });

            // Create donation record
            const donation = new Donation({
                user: req.userId,
                campaign: payment.campaign,
                amount: payment.amount,
                paymentMethod: 'razorpay',
                transactionId: razorpay_payment_id,
                status: 'completed',
                paymentDetails: {
                    razorpay_order_id,
                    razorpay_payment_id
                }
            });

            await donation.save();

            res.json({
                success: true,
                message: 'Payment verified successfully!',
                payment: {
                    id: payment._id,
                    amount: payment.amount,
                    transactionId: razorpay_payment_id,
                    campaign: campaign?.title
                }
            });

        } else {
            // Signature mismatch
            await Payment.findByIdAndUpdate(paymentId, {
                status: 'failed'
            });

            res.status(400).json({
                success: false,
                message: 'Payment verification failed'
            });
        }

    } catch (error) {
        console.error('Verify payment error:', error);
        res.status(500).json({
            success: false,
            message: 'Payment verification error'
        });
    }
});

// @route   GET /api/payment/transactions
// @desc    Get user's payment transactions
// @access  Private
router.get('/transactions', auth, async (req, res) => {
    try {
        const payments = await Payment.find({ user: req.userId })
            .populate('campaign', 'title category')
            .sort({ createdAt: -1 });

        res.json({
            success: true,
            payments
        });
    } catch (error) {
        console.error('Get transactions error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get transactions'
        });
    }
});

module.exports = router;