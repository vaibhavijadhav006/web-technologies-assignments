const mongoose = require('mongoose');

const CampaignSchema = new mongoose.Schema({
    title: {
        type: String,
        required: true,
        trim: true
    },
    description: {
        type: String,
        required: true
    },
    category: {
        type: String,
        required: true,
        enum: ['Education', 'Healthcare', 'Disaster Relief', 'Environment', 'Animal Welfare', 'Community Development', 'Children', 'Elderly', 'Other']
    },
    targetAmount: {
        type: Number,
        required: true,
        min: 100
    },
    currentAmount: {
        type: Number,
        default: 0
    },
    createdBy: {
        type: mongoose.Schema.Types.ObjectId,
        ref: 'User'
    },
    volunteers: [{
        type: mongoose.Schema.Types.ObjectId,
        ref: 'User'
    }],
    donorsCount: {
        type: Number,
        default: 0
    },
    donations: [{
        user: {
            type: mongoose.Schema.Types.ObjectId,
            ref: 'User'
        },
        amount: Number,
        date: {
            type: Date,
            default: Date.now
        }
    }],
    status: {
        type: String,
        enum: ['active', 'completed', 'cancelled'],
        default: 'active'
    },
    startDate: {
        type: Date,
        default: Date.now
    },
    endDate: {
        type: Date,
        required: true
    },
    createdAt: {
        type: Date,
        default: Date.now
    }
});

// Remove any problematic methods if they exist
module.exports = mongoose.model('Campaign', CampaignSchema);