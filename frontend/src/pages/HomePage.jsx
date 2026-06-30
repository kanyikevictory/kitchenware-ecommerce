import { CatalogError, CategorySection, HeroSection, NewsletterSection, ProductSection, ReviewsSection, ServiceStrip, WhyUsSection } from '../components/home/HomeSections'
import { useHomeCatalog } from '../hooks/useHomeCatalog'

export default function HomePage() {
  const { categories, featured, newest, isLoading, error, retry } = useHomeCatalog()
  return <>
  <HeroSection />
  <ServiceStrip />
  <CatalogError error={error} retry={retry} />
  <CategorySection categories={categories} isLoading={isLoading} />
  <ProductSection eyebrow="Our edit" title="Featured pieces" description="A considered collection of beautiful, hardworking essentials." products={featured.slice(0, 4)} isLoading={isLoading} soft />
  <ProductSection eyebrow="Customer favourites" title="Best sellers" description="The pieces our community returns to again and again." products={featured.slice(4, 8).length ? featured.slice(4, 8) : featured.slice(0, 4)} isLoading={isLoading} />
  <WhyUsSection />
  <ProductSection eyebrow="Just landed" title="New arrivals" description="Fresh additions for inspired cooking and effortless hosting." products={newest} isLoading={isLoading} soft />
  <ReviewsSection />
  <NewsletterSection />
</>
}
