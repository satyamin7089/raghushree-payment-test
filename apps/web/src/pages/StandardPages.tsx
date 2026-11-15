import { Link, useSearchParams } from 'react-router-dom';
import { useEffect, useState } from 'react';
import { CtaBanner, ProductCard, useScrollReveal } from '../components/CommonBlocks';
import { Icon } from '../components/Icon';
import { useSite } from '../components/SiteContext';
import { apiRequest } from '../lib/api';
import { SeoHead } from '../lib/seo';
import type { ProductDto } from '../types/api';

export function PrivacyPolicyPage() {
  const { config } = useSite();
  const legalName = config?.company?.legal_name || 'Raghushree Plast Products Pvt. Ltd.';
  const email = config?.company?.email || 'marketing@raghushree.com';

  return (
    <>
      <SeoHead
        title={`Privacy Policy | ${legalName}`}
        description={`Privacy policy for ${legalName} covering B2B enquiries, shipping quotes, and online transactions.`}
        canonicalPath="/privacy-policy/"
        company={config?.company}
      />
      <section className="section surface--white">
        <div className="container--narrow" style={{ paddingBlock: 'var(--space-8)' }}>
          <div className="eyebrow">Legal &amp; Compliance</div>
          <h1>Privacy Policy</h1>
          <p>
            {legalName} collects business contact, shipping, and quotation information solely to
            respond to material enquiries, calculate domestic shipping quotes, process prepaid
            orders via Razorpay, and coordinate commercial supply.
          </p>
          <h2>Information We Collect</h2>
          <p>
            When you submit an RFQ enquiry or place a sample/pack order, we record your name,
            company details, email, phone number, GSTIN (if provided), and shipping address. Payment
            card/UPI credentials are processed directly by Razorpay and never stored on our
            servers.
          </p>
          <h2>Contact Regarding Your Data</h2>
          <p>
            For questions regarding your commercial records or privacy rights, please contact{' '}
            <a href={`mailto:${email}`}>{email}</a>.
          </p>
        </div>
      </section>
    </>
  );
}

export function TermsOfUsePage() {
  const { config } = useSite();
  const legalName = config?.company?.legal_name || 'Raghushree Plast Products Pvt. Ltd.';

  return (
    <>
      <SeoHead
        title={`Terms of Use | ${legalName}`}
        description={`Commercial terms of use for ${legalName}.`}
        canonicalPath="/terms-of-use/"
        company={config?.company}
      />
      <section className="section surface--white">
        <div className="container--narrow" style={{ paddingBlock: 'var(--space-8)' }}>
          <div className="eyebrow">Commercial Terms</div>
          <h1>Terms of Use</h1>
          <p>
            All polymer raw material grades, forms (granules or powder), minimum order quantities,
            and pricing are subject to availability confirmation at the time of quotation or online
            checkout.
          </p>
          <h2>Online Pack Orders &amp; Shipping Estimates</h2>
          <p>
            Prepaid online orders calculate GST based on the product&apos;s configured rate and
            estimate courier charges via Shiprocket. Shipments exceeding 30 kg for pipe packets or
            bulk polymer lots require a direct commercial quotation.
          </p>
        </div>
      </section>
    </>
  );
}

export function SearchPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const q = searchParams.get('q') || '';
  const [input, setInput] = useState(q);
  const [results, setResults] = useState<ProductDto[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    setInput(q);
    if (!q.trim()) {
      setResults([]);
      return;
    }
    setLoading(true);
    apiRequest<{ items: ProductDto[] }>(
      `/api/v1/products?q=${encodeURIComponent(q.trim())}&include_legacy=1&limit=24`
    )
      .then((res) => setResults(res.items || []))
      .catch(() => setResults([]))
      .finally(() => setLoading(false));
  }, [q]);

  useScrollReveal(results.length);

  return (
    <>
      <SeoHead
        title={`Search Results${q ? `: ${q}` : ''} | Raghushree`}
        description="Search Raghushree polymer raw materials and industrial piping products."
        canonicalPath="/search/"
        robots="noindex,follow"
      />
      <section className="section surface--pearl">
        <div className="container">
          <div className="eyebrow">Catalog Search</div>
          <h1>Search Polymer &amp; Pipe Catalog</h1>
          <form
            className="catalog-search"
            style={{ maxWidth: '520px', marginBlock: 'var(--space-5)' }}
            onSubmit={(e) => {
              e.preventDefault();
              setSearchParams(input.trim() ? { q: input.trim() } : {});
            }}
          >
            <input
              type="search"
              value={input}
              placeholder="Search PP, HDPE, PVC, hoses..."
              onChange={(e) => setInput(e.target.value)}
            />
            <button type="submit" className="btn btn--primary">
              Search
            </button>
          </form>

          {loading && <p>Searching catalog…</p>}
          {!loading && q && results.length === 0 && (
            <p>No matching products found for &ldquo;{q}&rdquo;. Try browsing our full catalog.</p>
          )}
          {results.length > 0 && (
            <div className="product-grid">
              {results.map((p) => (
                <ProductCard key={p.id} product={p} />
              ))}
            </div>
          )}
        </div>
      </section>
    </>
  );
}

export function NotFoundPage() {
  return (
    <>
      <SeoHead
        title="Page Not Found | Raghushree"
        description="The requested page could not be found."
        robots="noindex,nofollow"
      />
      <section className="section surface--pearl">
        <div
          className="container--narrow"
          style={{ paddingBlock: 'var(--space-10)', textAlign: 'center' }}
        >
          <div className="eyebrow">404 Error</div>
          <h1>Page Not Found</h1>
          <p style={{ marginBottom: 'var(--space-6)' }}>
            The page you requested may have moved or does not exist. Explore our polymer catalog or
            contact our commercial team.
          </p>
          <div className="btn-group" style={{ justifyContent: 'center' }}>
            <Link className="btn btn--primary" to="/">
              <span>Return Home</span>
              <Icon name="arrow-right" className="icon-arrow" />
            </Link>
            <Link className="btn btn--dark" to="/products/">
              <span>View Products</span>
            </Link>
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
