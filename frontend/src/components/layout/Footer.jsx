import { Mail, MapPin, Phone } from 'lucide-react'
import { Link } from 'react-router-dom'

const shopLinks = [['All products', '/shop'], ['Cookware', '/categories/cookware'], ['Kitchen tools', '/categories/kitchen-tools'], ['New arrivals', '/new-arrivals']]
const helpLinks = [['Contact us', '/contact'], ['Delivery & returns', '/shipping-returns'], ['FAQs', '/faq'], ['Privacy policy', '/privacy']]

function FooterLinks({ title, links }) {
  return <div><h2 className="text-sm font-bold uppercase tracking-[0.18em] text-gold-500">{title}</h2><ul className="mt-5 space-y-3">{links.map(([label, to]) => <li key={to}><Link to={to} className="text-sm text-white/70 transition hover:text-white">{label}</Link></li>)}</ul></div>
}

export function Footer() {
  return <footer className="bg-navy-950 text-white"><div className="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
  <div>
    <Link to="/" className="font-display text-3xl font-bold">Kitchen<span className="text-gold-500">Store</span></Link>
    <p className="mt-4 max-w-xs text-sm leading-6 text-white/65">Thoughtfully selected kitchenware for cooks who care about craft, quality, and a beautiful table.</p>
    <div className="mt-5 flex gap-2">
      <a href="#instagram" aria-label="Instagram" className="grid size-9 place-items-center rounded-full border border-white/15 text-xs font-bold hover:text-gold-500">IG</a>
      <a href="#facebook" aria-label="Facebook" className="grid size-9 place-items-center rounded-full border border-white/15 text-xs font-bold hover:text-gold-500">FB</a>
      </div>
      </div>
      <FooterLinks title="Shop" links={shopLinks} />
      <FooterLinks title="Help" links={helpLinks} /><div>
        <h2 className="text-sm font-bold uppercase tracking-[0.18em] text-gold-500">
          Visit & contact</h2><ul className="mt-5 space-y-4 text-sm text-white/70">
          <li className="flex gap-3">
            <MapPin className="size-5 shrink-0 text-gold-500" />Kampala, Uganda</li><li>
              <a className="flex gap-3 hover:text-white" href="tel:+256700000000">
                <Phone className="size-5 text-gold-500" />+256 700 000 000</a></li><li>
                  <a className="flex gap-3 hover:text-white" href="mailto:hello@kitchenstore.ug">
                    <Mail className="size-5 text-gold-500" />hello@kitchenstore.ug</a></li>
                    </ul></div>
                    </div>
                    <div className="border-t border-white/10"><div className="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 text-xs text-white/50 sm:flex-row sm:justify-between">
                    <p>© {new Date().getFullYear()} Kitchen Store. All rights reserved.</p><p>Secure payments · Carefully packed · Locally supported</p></div></div>
                    </footer>
}
