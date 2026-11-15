import { useEffect, useState, type FormEvent } from 'react';
import { Link, useParams, useSearchParams } from 'react-router-dom';
import { CtaBanner, ProductCard, useScrollReveal } from '../components/CommonBlocks';
import { useSite } from '../components/SiteContext';
import { apiRequest } from '../lib/api';
import { SeoHead } from '../lib/seo';
import type { ProductDto } from '../types/api';

export function ProductsPage({
  taxonomyType,
}: {
  taxonomyType?: 'polymer_category' | 'material_grade' | 'material_form' | 'application';
}) {
  const { config } = useSite();
  const params = useParams<{ slug?: string }>();
  const [searchParams, setSearchParams] = useSearchParams();

  const activeCategory =
    taxonomyType === 'polymer_category'
      ? params.slug || ''
      : searchParams.get('polymer_cat') || '';
  const activeGrade = taxonomyType === 'material_grade' ? params.slug || '' : '';
  const activeForm = taxonomyType === 'material_form' ? params.slug || '' : '';
  const activeApplication = taxonomyType === 'application' ? params.slug || '' : '';
  const activeSearch = searchParams.get('product_q') || '';
  const activePage = Math.max(1, Number(searchParams.get('catalog_page') || 1) || 1);

  const [searchInput, setSearchInput] = useState(activeSearch);
  const [products, setProducts] = useState<ProductDto[]>([]);
  const [total, setTotal] = useState(0);
  const [totalPages, setTotalPages] = useState(1);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setSearchInput(activeSearch);
  }, [activeSearch]);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    const qs = new URLSearchParams({
      limit: '24',
      page: String(activePage),
    });
    if (activeCategory) qs.set('polymer_category', activeCategory);
    if (activeGrade) qs.set('material_grade', activeGrade);
    if (activeForm) qs.set('material_form', activeForm);
    if (activeApplication) qs.set('application', activeApplication);
    if (activeSearch) qs.set('q', activeSearch);

    apiRequest<{ items: ProductDto[]; total: number; totalPages: number }>(
      `/api/v1/products?${qs.toString()}`
    )
      .then((res) => {
        if (cancelled) return;
        setProducts(res.items || []);
        setTotal(res.total || 0);
        setTotalPages(res.totalPages || 1);
      })
      .catch(() => {
        if (!cancelled) {
          setProducts([]);
          setTotal(0);
        }
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [activeCategory, activeGrade, activeForm, activeApplication, activeSearch, activePage]);

  useScrollReveal(products.length);

  const categories = config?.categories || [];
  const groupedByCat = (() => {
    const groups: Array<{
      slug: string;
      title: string;
      type: 'polymer' | 'pipe';
      items: ProductDto[];
    }> = [];
    const bySlug = new Map<string, ProductDto[]>();
    for (const p of products) {
      const catSlug = p.primary_category?.slug || 'other';
      const arr = bySlug.get(catSlug) || [];
      arr.push(p);
      bySlug.set(catSlug, arr);
    }
    for (const cat of categories) {
      const items = bySlug.get(cat.slug);
      if (items && items.length > 0) {
        groups.push({ slug: cat.slug, title: cat.title, type: cat.category_type, items });
        bySlug.delete(cat.slug);
      }
    }
    for (const [slug, items] of bySlug.entries()) {
      groups.push({ slug, title: 'Additional Polymers', type: 'polymer', items });
    }
    return groups;
  })();

  const handleSearch = (e: FormEvent) => {
    e.preventDefault();
    const next = new URLSearchParams();
    if (activeCategory) next.set('polymer_cat', activeCategory);
    if (searchInput.trim()) next.set('product_q', searchInput.trim());
    setSearchParams(next);
  };

  const activeCatObj = categories.find((c) => c.slug === activeCategory);
  const pageTitle = activeCatObj
    ? `${activeCatObj.title} | Raghushree Products Catalog`
    : 'Polymer & Industrial Pipe Products Catalog | Raghushree';

  return (
    <>
      <SeoHead
        title={pageTitle}
        description="Browse Raghushree's complete industrial catalog of PVC Granules, PVC Resin, PP, LDPE, HDPE, Industrial Hoses, PVC Suction Pipes, PVC Tubing, and Lay Flat Pipes."
        canonicalPath="/products/"
        company={config?.company}
      />

      <section className="catalog-hero">
        <div className="container">
          <span className="eyebrow">Raghushree product collection</span>
          <h1>Materials &amp; pipes, thoughtfully sourced.</h1>
          <p>Explore our polymer materials, hoses and piping products by category.</p>
        </div>
      </section>

      <section className="section surface--pearl">
        <div className="container catalog-layout">
          <aside className="catalog-sidebar" aria-label="Product categories">
            <h2>Categories</h2>
            <Link
              className={`catalog-sidebar__link${activeCategory === '' ? ' is-active' : ''}`}
              to="/products/"
            >
              All products
            </Link>

            {(
              [
                ['polymer', 'Polymer materials'],
                ['pipe', 'Pipes & hoses'],
              ] as const
            ).map(([type, label]) => (
              <div key={type}>
                <h3>{label}</h3>
                {categories
                  .filter((c) => c.category_type === type)
                  .map((cat) => (
                    <Link
                      key={cat.slug}
                      className={`catalog-sidebar__link${
                        activeCategory === cat.slug ? ' is-active' : ''
                      }`}
                      to={`/products/?polymer_cat=${encodeURIComponent(cat.slug)}`}
                    >
                      {cat.title}
                    </Link>
                  ))}
              </div>
            ))}
          </aside>

          <div className="catalog-main">
            <form className="catalog-search" onSubmit={handleSearch}>
              <label className="screen-reader-text" htmlFor="catalog-search">
                Search products
              </label>
              <input
                id="catalog-search"
                type="search"
                value={searchInput}
                placeholder="Search products"
                onChange={(e) => setSearchInput(e.target.value)}
              />
              <button className="btn btn--primary" type="submit">
                Search
              </button>
            </form>

            <p className="catalog-count">
              {loading
                ? 'Loading catalog…'
                : `${total} ${total === 1 ? 'product' : 'products'} found`}
            </p>

            {!loading && products.length === 0 && (
              <div className="products-empty">
                <h2>No products found</h2>
                <p>Try another search or category, or contact us for assistance.</p>
              </div>
            )}

            {groupedByCat.map((group) => (
              <section key={group.slug} className="catalog-group">
                <div className="catalog-group__heading">
                  <div>
                    <span className="eyebrow">
                      {group.type === 'pipe' ? 'Pipes & hoses' : 'Polymer materials'}
                    </span>
                    <h2>{group.title}</h2>
                  </div>
                </div>
                <div className="product-grid">
                  {group.items.map((item) => (
                    <ProductCard key={item.id} product={item} />
                  ))}
                </div>
              </section>
            ))}

            {totalPages > 1 && (
              <nav aria-label="Catalog pages" className="catalog-pagination">
                {Array.from({ length: totalPages }, (_, i) => i + 1).map((pageNum) => (
                  <Link
                    key={pageNum}
                    aria-current={pageNum === activePage ? 'page' : undefined}
                    to={`/products/?${new URLSearchParams({
                      ...(activeCategory ? { polymer_cat: activeCategory } : {}),
                      ...(activeSearch ? { product_q: activeSearch } : {}),
                      catalog_page: String(pageNum),
                    }).toString()}`}
                  >
                    {pageNum}
                  </Link>
                ))}
              </nav>
            )}
          </div>
        </div>
      </section>

      <section className="section surface--white">
        <div className="container">
          <CtaBanner />
        </div>
      </section>
    </>
  );
}
