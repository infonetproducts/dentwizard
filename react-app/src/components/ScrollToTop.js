import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';

/**
 * ScrollToTop Component
 * 
 * Automatically scrolls the window to the top when the route changes.
 * This ensures users always start at the top of a new page rather than
 * maintaining the scroll position from the previous page.
 * 
 * Usage: Place this component inside <Router> in App.js
 */
function ScrollToTop() {
  const { pathname } = useLocation();

  useEffect(() => {
    // Scroll to top on route change
    window.scrollTo({
      top: 0,
      left: 0,
      behavior: 'instant' // Use 'instant' for immediate scroll, 'smooth' for animated
    });
  }, [pathname]); // Trigger whenever the path changes

  return null; // This component doesn't render anything
}

export default ScrollToTop;
