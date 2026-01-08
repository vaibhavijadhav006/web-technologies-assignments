const express = require('express');
const router = express.Router();
const User = require('../models/User');

// REGISTER - ULTRA SIMPLE
router.post('/register', async (req, res) => {
    console.log('📝 REGISTER REQUEST:', req.body.email);
    
    try {
        const { name, email, password, mobile, address, role } = req.body;
        
        // Check if user exists
        const existing = await User.findOne({ email });
        if (existing) {
            return res.json({ 
                success: false,
                message: 'User already exists' 
            });
        }
        
        // Create user (password will be hashed automatically by User model pre-save hook)
        const user = new User({
            name,
            email,
            password,
            mobile,
            address,
            role: role || 'user'
        });
        
        // Save to database
        console.log('💾 Saving to database...');
        await user.save();
        console.log('✅ USER SAVED TO DATABASE! ID:', user._id);
        
        // Verify save
        const verify = await User.findOne({ email });
        console.log('🔍 VERIFICATION: Found user?', !!verify);
        
        res.json({
            success: true,
            message: 'Registration successful!',
            user: {
                id: user._id,
                name: user.name,
                email: user.email,
                role: user.role
            }
        });
        
    } catch (error) {
        console.error('🔥 REGISTRATION ERROR:', error.message);
        res.status(500).json({
            success: false,
            message: 'Server error: ' + error.message
        });
    }
});

// LOGIN - ULTRA SIMPLE
router.post('/login', async (req, res) => {
    console.log('🔑 LOGIN ATTEMPT:', req.body.email);
    
    try {
        const { email, password } = req.body;
        
        // Find user
        const user = await User.findOne({ email });
        
        if (!user) {
            return res.json({
                success: false,
                message: 'Invalid credentials'
            });
        }
        
        // Compare password using the model method
        const isMatch = await user.comparePassword(password);
        
        if (isMatch) {
            console.log('✅ LOGIN SUCCESS for:', user.email);
            res.json({
                success: true,
                message: 'Login successful!',
                user: {
                    id: user._id,
                    name: user.name,
                    email: user.email,
                    role: user.role,
                    totalDonations: user.totalDonations
                }
            });
        } else {
            res.json({
                success: false,
                message: 'Invalid credentials'
            });
        }
        
    } catch (error) {
        console.error('🔥 LOGIN ERROR:', error.message);
        res.status(500).json({
            success: false,
            message: 'Server error'
        });
    }
});

// GET ALL USERS (for debugging)
router.get('/users', async (req, res) => {
    try {
        const users = await User.find({}, '-password');
        console.log('📊 FOUND', users.length, 'USERS IN DATABASE');
        
        res.json({
            success: true,
            count: users.length,
            users: users.map(u => ({
                id: u._id,
                name: u.name,
                email: u.email,
                role: u.role,
                createdAt: u.createdAt
            }))
        });
    } catch (error) {
        console.error('🔥 GET USERS ERROR:', error.message);
        res.status(500).json({
            success: false,
            message: 'Server error'
        });
    }
});

module.exports = router;