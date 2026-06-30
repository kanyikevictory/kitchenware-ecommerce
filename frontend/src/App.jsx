import { Route, Routes } from 'react-router-dom';
import Layout from './components/Layout';
import HomePage from './pages/HomePage';
import ShopPage from './pages/ShopPage';
import CategoryPage from './pages/CategoryPage';
import ProductDetailPage from './pages/ProductDetailPage';
import CartPage from './pages/CartPage';
import CheckoutPage from './pages/CheckoutPage';
import WishlistPage from './pages/WishlistPage';
import AboutPage from './pages/AboutPage';
import ContactPage from './pages/ContactPage';
import FaqPage from './pages/FaqPage';
import ShippingReturnsPage from './pages/ShippingReturnsPage';
import LegalPage from './pages/LegalPage';
import NotFoundPage from './pages/NotFoundPage';

export default function App(){return <Routes><Route element={<Layout/>}><Route index element={<HomePage/>}/><Route path="shop" element={<ShopPage/>}/><Route path="category/:slug" element={<CategoryPage/>}/><Route path="product/:id" element={<ProductDetailPage/>}/><Route path="cart" element={<CartPage/>}/><Route path="checkout" element={<CheckoutPage/>}/><Route path="wishlist" element={<WishlistPage/>}/><Route path="about" element={<AboutPage/>}/><Route path="contact" element={<ContactPage/>}/><Route path="faq" element={<FaqPage/>}/><Route path="shipping-returns" element={<ShippingReturnsPage/>}/><Route path="privacy" element={<LegalPage type="privacy"/>}/><Route path="terms" element={<LegalPage type="terms"/>}/><Route path="*" element={<NotFoundPage/>}/></Route></Routes>}
