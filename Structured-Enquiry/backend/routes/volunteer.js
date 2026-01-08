const express = require('express');
const router = express.Router();
const Campaign = require('../models/Campaign');
const User = require('../models/User');
const { auth } = require('../middleware/auth');

// @route   POST /api/volunteer/:campaignId
// @desc    Volunteer for a campaign
// @access  Private
router.post('/:campaignId', auth, async (req, res) => {
    try {
        const campaign = await Campaign.findById(req.params.campaignId);
        
        if (!campaign) {
            return res.status(404).json({
                success: false,
                message: 'Campaign not found'
            });
        }

        // Check if already volunteered
        if (campaign.volunteers.includes(req.userId)) {
            return res.status(400).json({
                success: false,
                message: 'Already volunteered for this campaign'
            });
        }

        // Add user to volunteers
        campaign.volunteers.push(req.userId);
        await campaign.save();

        // Update user's volunteered campaigns
        const user = await User.findById(req.userId);
        user.volunteeredCampaigns.push({
            campaignId: campaign._id,
            campaignTitle: campaign.title
        });
        user.totalVolunteered += 1;
        await user.save();

        res.json({
            success: true,
            message: 'Successfully volunteered for the campaign'
        });

    } catch (error) {
        console.error('Volunteer error:', error);
        res.status(500).json({
            success: false,
            message: 'Server error'
        });
    }
});

// @route   GET /api/volunteer/my-volunteering
// @desc    Get user's volunteering history
// @access  Private
router.get('/my-volunteering', auth, async (req, res) => {
    try {
        const user = await User.findById(req.userId)
            .select('volunteeredCampaigns totalVolunteered')
            .populate('volunteeredCampaigns.campaignId', 'title category');

        res.json({
            success: true,
            volunteering: user.volunteeredCampaigns,
            totalVolunteered: user.totalVolunteered
        });
    } catch (error) {
        console.error('Get volunteering error:', error);
        res.status(500).json({
            success: false,
            message: 'Server error'
        });
    }
});

module.exports = router;