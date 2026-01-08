import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { FaHome, FaUser, FaSignOutAlt, FaUserShield } from 'react-icons/fa';

const Navbar = () => {
    const navigate = useNavigate();
    const user = JSON.parse(localStorage.getItem('user') || '{}');

    const handleLogout = () => {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        navigate('/login');
    };

    if (!user || !user.email) {
        return null; // Don't show navbar if not logged in
    }

    return (
        <nav className="bg-gradient-to-r from-purple-600 to-blue-600 text-white p-4 shadow-lg">
            <div className="container mx-auto flex justify-between items-center">
                <div className="flex items-center space-x-4">
                    <Link to={user.role === 'admin' ? '/admin/dashboard' : '/dashboard'} 
                          className="text-xl font-bold flex items-center">
                        <FaHome className="mr-2" />
                        Donation App
                    </Link>
                    
                    <span className="text-sm bg-white/20 px-3 py-1 rounded-full">
                        {user.role === 'admin' ? <FaUserShield className="inline mr-1" /> : <FaUser className="inline mr-1" />}
                        {user.name} ({user.role})
                    </span>
                </div>
                
                <div className="flex items-center space-x-4">
                    {user.role === 'admin' ? (
                        <>
                            <Link to="/admin/dashboard" className="hover:bg-white/20 px-3 py-2 rounded">
                                Admin Dashboard
                            </Link>
                            <Link to="/dashboard" className="hover:bg-white/20 px-3 py-2 rounded">
                                View Campaigns
                            </Link>
                        </>
                    ) : (
                        <>
                            <Link to="/dashboard" className="hover:bg-white/20 px-3 py-2 rounded">
                                Dashboard
                            </Link>
                            <Link to="/profile" className="hover:bg-white/20 px-3 py-2 rounded">
                                My Profile
                            </Link>
                        </>
                    )}
                    
                    <button 
                        onClick={handleLogout}
                        className="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-gray-100 flex items-center"
                    >
                        <FaSignOutAlt className="mr-2" />
                        Logout
                    </button>
                </div>
            </div>
        </nav>
    );
};

export default Navbar;