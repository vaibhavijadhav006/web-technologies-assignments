const express = require('express');
const router = express.Router();
const { auth } = require('../middleware/auth');
const User = require('../models/User');
const Donation = require('../models/Donation');
const { check, validationResult } = require('express-validator');

// @route   GET /api/users/profile
// @desc    Get user profile
// @access  Private
// Add this route
router.get('/profile', auth, async (req, res) => {
    try {
        const user = await User.findById(req.userId)
            .select('-password')
            .populate('campaignsVolunteered', 'title category')
            .populate('donations.campaign', 'title');

        // Get donations
        const donations = await Donation.find({ user: req.userId })
            .populate('campaign', 'title category')
            .sort({ donatedAt: -1 });

        res.json({
            success: true,
            user,
            donations,
            stats: {
                totalDonated: user.totalDonations || 0,
                totalDonations: donations.length,
                campaignsVolunteered: user.campaignsVolunteered.length
            }
        });
    } catch (error) {
        console.error('Get profile error:', error);
        res.status(500).json({
            success: false,
            message: 'Server error'
        });
    }
});
// @route   PUT /api/users/profile
// @desc    Update user profile
// @access  Private
router.put('/profile', auth, [
    check('name', 'Name is required').optional().not().isEmpty(),
    check('mobile', 'Mobile must be 10 digits').optional().isLength({ min: 10, max: 10 }),
    check('address', 'Address is required').optional().not().isEmpty()
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({ 
                success: false,
                errors: errors.array() 
            });
        }
        
        const { name, mobile, address } = req.body;
        
        const updateData = {};
        if (name) updateData.name = name;
        if (mobile) updateData.mobile = mobile;
        if (address) updateData.address = address;
        
        const user = await User.findByIdAndUpdate(
            req.userId,
            { $set: updateData },
            { new: true, runValidators: true }
        ).select('-password');
        
        res.json({
            success: true,
            message: 'Profile updated successfully',
            user
        });
    } catch (error) {
        console.error('Update profile error:', error);
        res.status(500).json({ 
            success: false,
            message: 'Server error' 
        });
    }
});

// @route   PUT /api/users/:id
// @desc    Update user profile by ID (direct - no auth required for simplicity)
// @access  Public
router.put('/:id', async (req, res) => {
    try {
        const userId = req.params.id;
        const { name, mobile, address } = req.body;
        
        console.log('🔄 Updating user profile:', userId, { name, mobile, address });
        
        const user = await User.findById(userId);
        if (!user) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }
        
        // Update fields
        if (name) user.name = name;
        if (mobile) user.mobile = mobile;
        if (address) user.address = address;
        
        await user.save();
        
        console.log('✅ User profile updated:', userId);
        
        res.json({
            success: true,
            message: 'Profile updated successfully!',
            user: {
                id: user._id,
                name: user.name,
                email: user.email,
                role: user.role,
                mobile: user.mobile,
                address: user.address,
                totalDonations: user.totalDonations || 0
            }
        });
        
    } catch (error) {
        console.error('❌ Update user profile error:', error.message);
        res.status(500).json({
            success: false,
            message: 'Failed to update profile: ' + error.message
        });
    }
});

module.exports = router;