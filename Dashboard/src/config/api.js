const API = import.meta.env.DEV
    ? import.meta.env.VITE_API_URL
    : `${window.location.origin}/api`;

export default API;
