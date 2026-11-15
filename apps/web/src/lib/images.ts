import type { ProductDto } from '../types/api';

export interface PlaceholderImageEntry {
  localFile?: string;
  baseUrl: string;
  width: number;
  height: number;
  alt: string;
}

export const PLACEHOLDER_REGISTRY: Record<string, PlaceholderImageEntry> = {
  home_hero: {
    localFile: '/assets/images/bg-cta-3.jpg',
    baseUrl: 'https://images.unsplash.com/photo-1785900976288-31f0f42a7364',
    width: 1500,
    height: 670,
    alt: 'Industrial thermoplastic polymer processing and raw material production facility',
  },
  home_about: {
    baseUrl: 'https://images.unsplash.com/photo-1730705788367-dbd288c40ee7',
    width: 1200,
    height: 840,
    alt: 'Representative thermoplastic injection moulding machines in an industrial production facility',
  },
  home_quality: {
    baseUrl: 'https://images.unsplash.com/photo-1777464375799-768774f30542',
    width: 1200,
    height: 840,
    alt: 'Representative plastic processing machinery with hopper dryers and polymer raw material bags',
  },
  mega_menu_feature: {
    baseUrl: 'https://images.unsplash.com/photo-1675176785803-bffbbb0cd2f4',
    width: 800,
    height: 450,
    alt: 'Representative extruded polymer winding spools on an industrial production line',
  },
  about_hero: {
    baseUrl: 'https://images.unsplash.com/photo-1717386255773-1e3037c81788',
    width: 1400,
    height: 920,
    alt: 'Representative automated industrial manufacturing and material forming production line',
  },
  about_overview: {
    baseUrl: 'https://images.unsplash.com/photo-1777464375799-768774f30542',
    width: 1200,
    height: 840,
    alt: 'Representative plastic processing line with raw material hoppers and industrial equipment',
  },
  about_quality: {
    baseUrl: 'https://images.unsplash.com/photo-1781330173666-11f8818d6eb2',
    width: 1200,
    height: 840,
    alt: 'Representative industrial testing and process control instrumentation',
  },
  about_supply: {
    baseUrl: 'https://images.unsplash.com/photo-1689942010216-dc412bb1e7a9',
    width: 1200,
    height: 840,
    alt: 'Representative industrial warehouse pallet racking and raw material logistics handling',
  },
  contact_hero: {
    baseUrl: 'https://images.unsplash.com/photo-1689942010216-dc412bb1e7a9',
    width: 1280,
    height: 800,
    alt: 'Representative industrial polymer warehouse logistics and B2B order dispatch',
  },
  products_hero: {
    baseUrl: 'https://images.unsplash.com/photo-1785900976288-31f0f42a7364',
    width: 1280,
    height: 800,
    alt: 'Representative stacked polymer resin bags ready for industrial manufacturing supply',
  },
  polymer_pp: {
    baseUrl: 'https://images.unsplash.com/photo-1675176785803-bffbbb0cd2f4',
    width: 1000,
    height: 625,
    alt: 'Representative polypropylene tape and raffia extrusion winding machinery',
  },
  polymer_hdpe: {
    baseUrl: 'https://images.unsplash.com/photo-1785900976288-31f0f42a7364',
    width: 1000,
    height: 625,
    alt: 'Representative palletized high-density polyethylene resin sacks for industrial moulding and extrusion',
  },
  polymer_ldpe: {
    baseUrl: 'https://images.unsplash.com/photo-1675176785665-8d6aef6b7d8a',
    width: 1000,
    height: 625,
    alt: 'Representative extruded polyethylene film rolls in an industrial production hall',
  },
  polymer_lldpe: {
    baseUrl: 'https://images.unsplash.com/photo-1675176788145-928336d4ba14',
    width: 1000,
    height: 625,
    alt: 'Representative extruded linear low-density polyethylene spools and flexible packaging material',
  },
  polymer_pvc: {
    baseUrl: 'https://images.unsplash.com/photo-1779718811629-92d973f0badb',
    width: 1000,
    height: 625,
    alt: 'Representative extruded rigid plastic industrial pipes manufactured from polymer compounds',
  },
  polymer_pet: {
    baseUrl: 'https://images.unsplash.com/photo-1536939459926-301728717817',
    width: 1000,
    height: 625,
    alt: 'Representative blow-moulded clear PET packaging containers on a production lot',
  },
  polymer_recycled: {
    baseUrl: 'https://images.unsplash.com/photo-1760307837830-5d5d31fcf25f',
    width: 1000,
    height: 625,
    alt: 'Representative sorted polyolefin plastic feedstock prepared for recycling and reprocessing',
  },
  app_injection: {
    baseUrl: 'https://images.unsplash.com/photo-1730705788367-dbd288c40ee7',
    width: 800,
    height: 450,
    alt: 'Representative industrial injection moulding machinery line',
  },
  app_blow: {
    baseUrl: 'https://images.unsplash.com/photo-1777464375799-768774f30542',
    width: 800,
    height: 450,
    alt: 'Representative hollow container blow moulding equipment and hopper dryers',
  },
  app_extrusion: {
    baseUrl: 'https://images.unsplash.com/photo-1787672357830-ef4281855d47',
    width: 800,
    height: 450,
    alt: 'Representative continuous industrial extrusion and profile forming rollers',
  },
  app_film: {
    baseUrl: 'https://images.unsplash.com/photo-1675176785665-8d6aef6b7d8a',
    width: 800,
    height: 450,
    alt: 'Representative industrial packaging film rolls in a manufacturing hall',
  },
};

export function getSlotImage(slotKey: string): { src: string; alt: string; width: number; height: number } {
  const entry = PLACEHOLDER_REGISTRY[slotKey] || PLACEHOLDER_REGISTRY.home_hero;
  if (entry.localFile) {
    return { src: entry.localFile, alt: entry.alt, width: entry.width, height: entry.height };
  }
  const url = `${entry.baseUrl}?auto=format&fit=crop&w=${entry.width}&h=${entry.height}&q=80`;
  return { src: url, alt: entry.alt, width: entry.width, height: entry.height };
}

export function getProductImage(product: ProductDto): { src: string; alt: string } {
  if (product.featured_image_url) {
    return { src: product.featured_image_url, alt: product.featured_image_alt || product.title };
  }
  const slot = getSlotImage(product.image_slot || 'polymer_hdpe');
  return { src: slot.src, alt: product.title || slot.alt };
}
