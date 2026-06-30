import { useMemo, useState } from 'react';
import { Heart, Minus, Plus, ShieldCheck, Truck, Undo2 } from 'lucide-react';
import { Link, useParams } from 'react-router-dom';
import { products, formatMoney } from '../data/catalog';
import { useApp } from '../context/AppContext';
import Rating from '../components/Rating';
import ProductCard from '../components/ProductCard';

export default function ProductDetailPage() {
  const { id } = useParams();
  const product = useMemo(() => products.find((item) => String(item.id) === id), [id]);
  const [quantity, setQuantity] = useState(1);
  const [activeImage, setActiveImage] = useState(0);
  const { addToCart, toggleWishlist, wishlist } = useApp();
  if (!product) return <div className="mx-auto max-w-7xl px-6 py-24">Product not found. <Link className="text-navy underline" to="/shop">Return to shop</Link></div>;
  const gallery = [product.image, product.image.replace('Product', 'Detail'), product.image.replace('Product', 'Lifestyle')];
  const wished = wishlist.some((item) => item.id === product.id);
  return <main className="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10">
    <nav className="mb-8 text-sm text-navy/70"><Link to="/">Home</Link> / <Link to={`/category/${product.category}`}>{product.category}</Link> / <span>{product.name}</span></nav>
    <div className="grid gap-12 lg:grid-cols-2">
      <section><div className="overflow-hidden rounded-2xl bg-sand"><img key={activeImage} src={gallery[activeImage]} alt={`${product.name} view ${activeImage + 1}`} className="aspect-square w-full object-cover animate-[fadeIn_.35s_ease]" /></div>
        <div className="mt-4 grid grid-cols-3 gap-3">{gallery.map((image, index) => <button key={image} onClick={() => setActiveImage(index)} className={`overflow-hidden rounded-xl border-2 ${activeImage === index ? 'border-navy' : 'border-transparent'}`}><img src={image} alt={`${product.name} thumbnail ${index + 1}`} className="aspect-square object-cover" /></button>)}</div>
      </section>
      <section className="lg:pt-4"><p className="text-xs uppercase tracking-[.25em] text-navy/60">{product.brand} · {product.material}</p><h1 className="mt-3 font-serif text-4xl text-navy sm:text-5xl">{product.name}</h1>
        <div className="mt-5 flex items-center gap-3"><Rating value={product.rating} /><span className="text-sm text-charcoal/60">{product.reviews} reviews</span></div>
        <div className="mt-6 flex items-end gap-3"><strong className="text-2xl text-charcoal">{formatMoney(product.price)}</strong>{product.originalPrice && <span className="text-charcoal/45 line-through">{formatMoney(product.originalPrice)}</span>}</div>
        <p className="mt-6 leading-7 text-charcoal/75">{product.description || 'Thoughtfully made for everyday rituals, this kitchen essential combines enduring materials with quietly beautiful design.'}</p>
        <div className="mt-7 overflow-hidden rounded-xl border border-navy/10 text-sm"><dl className="grid grid-cols-2">{[['Material', product.material], ['Category', product.category], ['Availability', product.inStock ? 'In stock' : 'Out of stock'], ['Care', 'Hand wash recommended']].map(([key,value]) => <div className="border-b border-navy/10 p-4 odd:border-r" key={key}><dt className="text-charcoal/50">{key}</dt><dd className="mt-1 font-medium capitalize text-navy">{value}</dd></div>)}</dl></div>
        <div className="mt-8 flex flex-wrap gap-3"><div className="flex items-center rounded-full border border-navy/20"><button onClick={() => setQuantity(Math.max(1, quantity - 1))} className="p-3" aria-label="Decrease quantity"><Minus size={17}/></button><span className="w-10 text-center">{quantity}</span><button onClick={() => setQuantity(quantity + 1)} className="p-3" aria-label="Increase quantity"><Plus size={17}/></button></div><button onClick={() => addToCart(product, quantity)} className="flex-1 rounded-full bg-terracotta px-7 py-3 font-medium text-white">Add to cart</button><button onClick={() => toggleWishlist(product)} className="rounded-full border border-navy/20 p-3 text-navy" aria-label="Toggle wishlist"><Heart className={wished ? 'fill-navy' : ''}/></button></div>
        <div className="mt-8 grid grid-cols-3 gap-3 border-t border-navy/10 pt-6 text-center text-xs text-navy"><span><Truck className="mx-auto mb-2" size={20}/>Fast delivery</span><span><Undo2 className="mx-auto mb-2" size={20}/>Easy returns</span><span><ShieldCheck className="mx-auto mb-2" size={20}/>Secure payment</span></div>
      </section>
    </div>
    <section className="py-20"><h2 className="font-serif text-3xl text-navy">You may also love</h2><div className="mt-8 grid grid-cols-2 gap-5 lg:grid-cols-4">{products.filter(p => p.id !== product.id).slice(0,4).map(p => <ProductCard key={p.id} product={p}/>)}</div></section>
  </main>;
}
