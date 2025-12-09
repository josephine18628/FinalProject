import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';

const Sidebar = () => {
  const { user, logout, isAdmin } = useAuth();
  const location = useLocation();

  const navItems = [
    { path: '/dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', label: 'Dashboard' },
    { path: '/admin', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', label: 'Admin', adminOnly: true },
  ];

  const isActive = (path) => location.pathname === path || location.pathname.startsWith(path + '/');

  return (
    <div className="fixed left-0 top-0 h-full w-20 bg-white rounded-r-3xl shadow-soft flex flex-col items-center py-6 z-10">
      {/* User Avatar */}
      <div className="mb-8">
        <img
          src={`https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&h=100&fit=crop`}
          alt="User"
          className="w-12 h-12 rounded-full object-cover border-2 border-gray-200"
        />
      </div>

      {/* Navigation Icons */}
      <nav className="flex-1 flex flex-col items-center space-y-6">
        {navItems
          .filter(item => !item.adminOnly || isAdmin())
          .map((item) => (
            <Link
              key={item.path}
              to={item.path}
              className={`p-3 rounded-lg transition-colors ${
                isActive(item.path)
                  ? 'bg-primary-blue text-white'
                  : 'text-text-gray hover:bg-blue-50 hover:text-primary-blue'
              }`}
              title={item.label}
            >
              <svg
                className="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d={item.icon}
                />
              </svg>
            </Link>
          ))}
      </nav>

      {/* Logout Button */}
      <button
        onClick={logout}
        className="p-3 rounded-lg text-text-gray hover:bg-red-50 hover:text-red-600 transition-colors"
        title="Logout"
      >
        <svg
          className="w-6 h-6"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
          />
        </svg>
      </button>
    </div>
  );
};

export default Sidebar;

