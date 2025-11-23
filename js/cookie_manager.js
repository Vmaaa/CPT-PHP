const CookieManager = {
  fetchWithAuth: (url, options = {}) => {
    return fetch(url, {
      ...options,
      credentials: "include", // ENVÍA cookies HTTP-only automáticamente
      headers: {
        ...(options.headers || {}),
      },
    });
  },
  logout: () => {
    return fetch("/api/v1/account/logout/", {
      method: "POST",
      credentials: "include",
    }).then(() => window.location.reload());
  },
};
