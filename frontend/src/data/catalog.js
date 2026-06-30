const image = (text, size = '800x800') => `https://placehold.co/${size}/F0E8D8/1B2B45?text=${encodeURIComponent(text)}`

export const categories = [
  { id: 1, slug: 'cookware', name: 'Cookware', count: 28, description: 'Heirloom pieces engineered for everyday ritual.', image: image('Artisan Cookware', '1000x1200') },
  { id: 2, slug: 'bakeware', name: 'Bakeware', count: 16, description: 'Beautiful bakes, even heat, effortless release.', image: image('Stone Bakeware') },
  { id: 3, slug: 'utensils', name: 'Utensils', count: 34, description: 'Balanced tools designed for a lifetime of cooking.', image: image('Kitchen Utensils') },
  { id: 4, slug: 'appliances', name: 'Appliances', count: 12, description: 'Quiet power for considered kitchens.', image: image('Small Appliances') },
  { id: 5, slug: 'storage', name: 'Storage', count: 22, description: 'Order, freshness, and calm on every shelf.', image: image('Pantry Storage') },
  { id: 6, slug: 'tableware', name: 'Tableware', count: 31, description: 'Make the everyday table feel like an occasion.', image: image('Elegant Tableware') },
]

const base = [
  ['Heritage Dutch Oven', 'cookware', 189, 229, 'Enameled cast iron', 'Maison', 4.9, 148, ['Navy', 'Cream'], ['5.5 qt', '7.5 qt']],
  ['Copper Core Sauté Pan', 'cookware', 149, null, 'Copper-bonded steel', 'Atelier', 4.8, 96, ['Copper', 'Silver'], ['10 in', '12 in']],
  ['No. 8 Cast Iron Skillet', 'cookware', 84, null, 'Seasoned cast iron', 'Foundry', 4.9, 207, ['Charcoal'], ['10 in', '12 in']],
  ['Fluted Stoneware Set', 'bakeware', 98, 122, 'High-fired stoneware', 'Maison', 4.7, 74, ['Cream', 'Navy'], ['3 piece']],
  ['French Rolling Pin', 'bakeware', 38, null, 'FSC maple wood', 'Atelier', 4.8, 51, ['Natural'], ['Standard']],
  ['Walnut Prep Tools', 'utensils', 64, null, 'Black walnut', 'Maison', 4.9, 118, ['Walnut'], ['5 piece']],
  ['Chef’s Precision Knife', 'utensils', 132, 165, 'German stainless steel', 'Foundry', 4.9, 184, ['Navy', 'Walnut'], ['8 in']],
  ['Countertop Kettle', 'appliances', 119, null, 'Brushed stainless steel', 'Atelier', 4.6, 82, ['Navy', 'Cream'], ['1.7 L']],
  ['Quiet Grind Mill', 'appliances', 176, null, 'Stainless steel', 'Maison', 4.8, 63, ['Navy'], ['Standard']],
  ['Glass Pantry Collection', 'storage', 72, 90, 'Borosilicate glass', 'Atelier', 4.7, 109, ['Clear', 'Walnut'], ['6 piece']],
  ['Linen Bread Keeper', 'storage', 44, null, 'Stoneware and linen', 'Maison', 4.5, 37, ['Cream'], ['Large']],
  ['Hand-Glazed Dinner Set', 'tableware', 156, null, 'Hand-glazed ceramic', 'Maison', 4.9, 91, ['Cream', 'Navy'], ['12 piece']],
]

export const products = base.map((p, index) => ({
  id: index + 1, name: p[0], slug: p[0].toLowerCase().replaceAll('’', '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''),
  category: p[1], price: p[2], originalPrice: p[3], material: p[4], brand: p[5], rating: p[6], reviews: p[7], colors: p[8], sizes: p[9],
  image: image(p[0]), images: [image(p[0]), image(`${p[0]} Detail`), image(`${p[0]} In Kitchen`)],
  featured: index < 8, bestSeller: [0, 2, 5, 6, 11].includes(index), inStock: index !== 8, stock: index === 4 ? 3 : 18,
  description: `A considered ${p[4].toLowerCase()} essential, designed to perform beautifully and live comfortably in your kitchen.`,
}))

export const reviews = [
  { name: 'Amelia R.', location: 'Brooklyn, NY', quote: 'It cooks as beautifully as it looks. The kind of piece you leave on the stove.', rating: 5 },
  { name: 'Marcus T.', location: 'Austin, TX', quote: 'Exceptional balance, thoughtful details, and packaging that felt genuinely special.', rating: 5 },
  { name: 'Sofia L.', location: 'Portland, OR', quote: 'Maison & Flame made our weeknight kitchen feel like the best room in the house.', rating: 5 },
]

export const money = (value) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value)
