import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react';

const AppContext = createContext(null);

const CART_STORAGE_KEY = 'maison-flame-cart';
const WISHLIST_STORAGE_KEY = 'maison-flame-wishlist';
const TOAST_DURATION = 2500;

function readStoredArray(key) {
  if (typeof window === 'undefined') {
    return [];
  }

  try {
    const storedValue = window.localStorage.getItem(key);
    const parsedValue = storedValue ? JSON.parse(storedValue) : [];

    return Array.isArray(parsedValue) ? parsedValue : [];
  } catch {
    return [];
  }
}

function createLineKey(product, options = {}) {
  const color = options.color ?? product.color ?? 'default';
  const size = options.size ?? product.size ?? 'default';

  return `${product.id}-${color}-${size}`;
}

export function AppProvider({ children }) {
  const [cartItems, setCartItems] = useState(() =>
    readStoredArray(CART_STORAGE_KEY),
  );
  const [wishlist, setWishlist] = useState(() =>
    readStoredArray(WISHLIST_STORAGE_KEY),
  );
  const [toasts, setToasts] = useState([]);
  const [isCartOpen, setIsCartOpen] = useState(false);

  useEffect(() => {
    window.localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cartItems));
  }, [cartItems]);

  useEffect(() => {
    window.localStorage.setItem(
      WISHLIST_STORAGE_KEY,
      JSON.stringify(wishlist),
    );
  }, [wishlist]);

  const removeToast = useCallback((toastId) => {
    setToasts((currentToasts) =>
      currentToasts.filter((toast) => toast.id !== toastId),
    );
  }, []);

  const showToast = useCallback(
    (message, productName = '') => {
      const toastId = `${Date.now()}-${Math.random()}`;

      setToasts((currentToasts) => [
        ...currentToasts,
        { id: toastId, message, productName },
      ]);

      window.setTimeout(() => removeToast(toastId), TOAST_DURATION);
    },
    [removeToast],
  );

  const addToCart = useCallback(
    (product, quantity = 1, options = {}) => {
      const safeQuantity = Math.max(1, Number(quantity) || 1);
      const lineKey = createLineKey(product, options);

      setCartItems((currentItems) => {
        const existingItem = currentItems.find(
          (item) => item.lineKey === lineKey,
        );

        if (existingItem) {
          return currentItems.map((item) =>
            item.lineKey === lineKey
              ? { ...item, quantity: item.quantity + safeQuantity }
              : item,
          );
        }

        return [
          ...currentItems,
          {
            ...product,
            ...options,
            lineKey,
            quantity: safeQuantity,
          },
        ];
      });

      showToast('Added to cart', product.name);
    },
    [showToast],
  );

  const updateQuantity = useCallback((identifier, quantity) => {
    const safeQuantity = Math.max(1, Number(quantity) || 1);

    setCartItems((currentItems) =>
      currentItems.map((item) =>
        item.lineKey === identifier || item.id === identifier
          ? { ...item, quantity: safeQuantity }
          : item,
      ),
    );
  }, []);

  const removeFromCart = useCallback((identifier) => {
    setCartItems((currentItems) =>
      currentItems.filter(
        (item) => item.lineKey !== identifier && item.id !== identifier,
      ),
    );
  }, []);

  const clearCart = useCallback(() => {
    setCartItems([]);
  }, []);

  const toggleWishlist = useCallback(
    (product) => {
      setWishlist((currentItems) => {
        const isSaved = currentItems.some((item) => item.id === product.id);

        if (isSaved) {
          return currentItems.filter((item) => item.id !== product.id);
        }

        showToast('Saved to wishlist', product.name);
        return [...currentItems, product];
      });
    },
    [showToast],
  );

  const cartCount = useMemo(
    () => cartItems.reduce((total, item) => total + item.quantity, 0),
    [cartItems],
  );

  const cartSubtotal = useMemo(
    () =>
      cartItems.reduce(
        (total, item) => total + Number(item.price) * item.quantity,
        0,
      ),
    [cartItems],
  );

  const contextValue = useMemo(
    () => ({
      // State
      cart: cartItems,
      cartItems,
      wishlist,
      toasts,
      isCartOpen,
      cartOpen: isCartOpen,

      // Derived values
      cartCount,
      cartSubtotal,
      wishlistCount: wishlist.length,

      // Cart actions
      addToCart,
      updateQuantity,
      removeFromCart,
      clearCart,
      openCart: () => setIsCartOpen(true),
      closeCart: () => setIsCartOpen(false),
      setIsCartOpen,
      setCartOpen: setIsCartOpen,

      // Wishlist and notification actions
      toggleWishlist,
      showToast,
      addToast: showToast,
      removeToast,
    }),
    [
      addToCart,
      cartCount,
      cartItems,
      cartSubtotal,
      clearCart,
      isCartOpen,
      removeFromCart,
      removeToast,
      showToast,
      toasts,
      toggleWishlist,
      updateQuantity,
      wishlist,
    ],
  );

  return (
    <AppContext.Provider value={contextValue}>
      {children}
    </AppContext.Provider>
  );
}

// eslint-disable-next-line react-refresh/only-export-components
export function useApp() {
  const context = useContext(AppContext);

  if (!context) {
    throw new Error('useApp must be used inside AppProvider.');
  }

  return context;
}
