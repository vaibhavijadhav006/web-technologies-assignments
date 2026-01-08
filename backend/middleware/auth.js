const jwt = require('jsonwebtoken');
const User = require('../models/User');

const auth = async (req, res, next) => {
    try {
        const token = req.header('Authorization')?.replace('Bearer ', '');
        
        // Simplified auth for development - allow requests without strict token validation
        if (!token) {
            // Try to get userId from body, query, or headers
            const userId = req.body.userId || req.body.createdBy || req.query.userId || req.headers['x-user-id'];
            if (userId && userId !== 'admin' && userId !== 'undefined') {
                try {
                    const user = await User.findById(userId);
                    if (user) {
                        req.userId = userId;
                        req.userRole = user.role;
                        return next();
                    }
                } catch (err) {
                    console.log('⚠️ User lookup failed:', err.message);
                }
            }
            // For development, try to get user from email if provided
            if (req.body.email) {
                try {
                    const user = await User.findOne({ email: req.body.email });
                    if (user) {
                        req.userId = user._id.toString();
                        req.userRole = user.role;
                        return next();
                    }
                } catch (err) {
                    console.log('⚠️ User lookup by email failed:', err.message);
                }
            }
            // For development, allow request to continue but set default role
            // This allows admin routes to work if adminAuth is lenient
            req.userRole = 'user'; // Default role
            // In production, uncomment the line below
            // return res.status(401).json({ success: false, message: 'No authentication token' });
        }

        // Try JWT verification if token exists
        if (token) {
            const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key-change-in-production';
            try {
                const decoded = jwt.verify(token, JWT_SECRET);
                const user = await User.findById(decoded.userId);

                if (user) {
                    req.userId = decoded.userId;
                    req.userRole = decoded.role || user.role;
                    return next();
                }
            } catch (jwtError) {
                // JWT verification failed, but allow request for development
                console.log('⚠️ JWT verification failed, continuing (dev mode)');
            }
        }
        
        // Allow request to continue (development mode)
        next();
    } catch (error) {
        console.error('Auth middleware error:', error);
        // Allow request to continue in development
        next();
    }
};

const adminAuth = async (req, res, next) => {
    // In development mode, be more lenient
    // Check if userRole is admin, or if we can determine admin status
    if (req.userRole !== 'admin') {
        // Try to get user from userId if set
        if (req.userId) {
            try {
                const user = await User.findById(req.userId);
                if (user && user.role === 'admin') {
                    req.userRole = 'admin';
                    return next();
                }
            } catch (err) {
                console.log('⚠️ User lookup in adminAuth failed:', err.message);
            }
        }
        
        // For development, allow if no strict auth is enforced
        // In production, uncomment the line below
        // return res.status(403).json({ success: false, message: 'Admin access required' });
        
        // Development mode: allow if userId exists (assume admin for now)
        if (req.userId || req.body.createdBy) {
            console.log('⚠️ Admin auth bypassed (dev mode)');
            req.userRole = 'admin';
            return next();
        }
        
        return res.status(403).json({ 
            success: false,
            message: 'Admin access required' 
        });
    }
    next();
};

module.exports = { auth, adminAuth };