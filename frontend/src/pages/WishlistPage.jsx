import { Heart } from 'lucide-react';
import { Link } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import ProductCard from '../components/ProductCard';

export default function WishlistPage(){const { wishlist }=useApp();return <main className="mx-auto min-h-[60vh] max-w-7xl px-5 py-12 sm:px-8"><h1 className="font-serif text-5xl text-navy">Saved for later</h1>{wishlist.length?<div className="mt-10 grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4">{wishlist.map(p=><ProductCard product={p} key={p.id}/>)}</div>:<div className="py-24 text-center"><Heart className="mx-auto text-navy" size={44}/><p className="mt-5 text-charcoal/65">Your wishlist is ready for a little inspiration.</p><Link className="mt-6 inline-block text-navy underline" to="/shop">Explore the shop</Link></div>}</main>}
