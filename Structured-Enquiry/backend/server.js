const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
require('dotenv').config();

// Import Models
const User = require('./models/User');
const Campaign = require('./models/Campaign');
const Donation = require('./models/Donation');

// Import Routes
const authRoutes = require('./routes/auth');
const campaignRoutes = require('./routes/campaigns');
const donationRoutes = require('./routes/donation');
const paymentRoutes = require('./routes/payment');
const userRoutes = require('./routes/users');
const adminRoutes = require('./routes/admin');
const volunteerRoutes = require('./routes/volunteer');

// Initialize Express App
const app = express();

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// MongoDB Connection
console.log('🔄 Connecting to MongoDB...');
mongoose.connect(process.env.MONGODB_URI || 'mongodb://127.0.0.1:27017/donation_app', {
    useNewUrlParser: true,
    useUnifiedTopology: true
})
.then(() => {
    console.log('✅ MongoDB Connected Successfully!');
    console.log('Database:', mongoose.connection.db?.databaseName || 'donation_app');
    console.log('Host:', mongoose.connection.host || 'localhost');
})
.catch(err => {
    console.error('❌ MongoDB Connection Error:', err.message);
    process.exit(1);
});

// MongoDB Connection Event Handlers
mongoose.connection.on('connected', () => {
    console.log('✅ Mongoose connected to MongoDB');
});

mongoose.connection.on('error', (err) => {
    console.error('❌ Mongoose connection error:', err);
});

mongoose.connection.on('disconnected', () => {
    console.log('⚠️ Mongoose disconnected from MongoDB');
});

// Routes
app.use('/api/auth', authRoutes);
app.use('/api/campaigns', campaignRoutes);
app.use('/api/donations', donationRoutes);
app.use('/api/payment', paymentRoutes);
app.use('/api/users', userRoutes);
app.use('/api/admin', adminRoutes);
app.use('/api/volunteer', volunteerRoutes);

// Health Check Endpoint
app.get('/api/health', (req, res) => {
    res.json({
        status: 'OK',
        server: 'Running',
        database: mongoose.connection.readyState === 1 ? 'Connected' : 'Disconnected',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Root Endpoint
app.get('/', (req, res) => {
        res.json({
        message: 'Welcome to Donation App API',
        version: '1.0.0',
        endpoints: {
            auth: '/api/auth',
            campaigns: '/api/campaigns',
            donations: '/api/donations',
            payment: '/api/payment',
            users: '/api/users',
            admin: '/api/admin',
            volunteer: '/api/volunteer',
            health: '/api/health'
        }
    });
});

// Error Handling Middleware
app.use((err, req, res, next) => {
    console.error('❌ Error:', err);
    res.status(err.status || 500).json({
            success: false,
        message: err.message || 'Internal Server Error',
        ...(process.env.NODE_ENV === 'development' && { stack: err.stack })
    });
});

// 404 Handler
app.use((req, res) => {
    res.status(404).json({
            success: false,
        message: 'Route not found'
        });
});

// Start Server
const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
    console.log('='.repeat(60));
    console.log(`🚀 Server running on http://localhost:${PORT}`);
    console.log('='.repeat(60));
    console.log('📌 Available Endpoints:');
    console.log(`   GET    http://localhost:${PORT}/api/health`);
    console.log(`   POST   http://localhost:${PORT}/api/auth/register`);
    console.log(`   POST   http://localhost:${PORT}/api/auth/login`);
    console.log(`   GET    http://localhost:${PORT}/api/campaigns`);
    console.log(`   POST   http://localhost:${PORT}/api/campaigns`);
    console.log(`   POST   http://localhost:${PORT}/api/donations`);
    console.log(`   POST   http://localhost:${PORT}/api/payment/create-order`);
    console.log(`   GET    http://localhost:${PORT}/api/users/profile`);
    console.log(`   GET    http://localhost:${PORT}/api/admin/dashboard`);
    console.log('='.repeat(60));
});

// Graceful Shutdown
process.on('SIGTERM', () => {
    console.log('SIGTERM signal received: closing HTTP server');
    mongoose.connection.close(() => {
        console.log('MongoDB connection closed');
        process.exit(0);
    });
});

process.on('SIGINT', () => {
    console.log('\nSIGINT signal received: closing HTTP server');
    mongoose.connection.close(() => {
        console.log('MongoDB connection closed');
        process.exit(0);
    });
});

module.exports = app;
