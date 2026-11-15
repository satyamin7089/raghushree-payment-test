import { useEffect } from 'react';
import { BrowserRouter, Navigate, Route, Routes, useLocation, useParams } from 'react-router-dom';
import { AuthProvider } from './components/AuthContext';
import { Layout } from './components/Layout';
import { SiteProvider } from './components/SiteContext';
import { AboutPage } from './pages/AboutPage';
import { ContactPage } from './pages/ContactPage';
import { HomePage } from './pages/HomePage';
import { ProductDetailPage } from './pages/ProductDetailPage';
import { ProductsPage } from './pages/ProductsPage';
import {
  NotFoundPage,
  PrivacyPolicyPage,
  SearchPage,
  TermsOfUsePage,
} from './pages/StandardPages';
import { AdminDashboard } from './pages/admin/AdminDashboard';
import { AdminLoginPage } from './pages/admin/AdminLoginPage';

function ScrollToTop() {
  const { pathname, hash } = useLocation();
  useEffect(() => {
    if (hash) {
      const target = document.getElementById(hash.slice(1));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
        return;
      }
    }
    window.scrollTo(0, 0);
  }, [pathname, hash]);
  return null;
}

function LegacyProductRedirect() {
  const { slug } = useParams<{ slug: string }>();
  return <Navigate to={slug ? `/products/${slug}/` : '/products/'} replace />;
}

export function AppRoutes() {
  return (
    <>
      <ScrollToTop />
      <Routes>
        {/* Admin routes */}
        <Route path="/admin/login" element={<AdminLoginPage />} />
        <Route path="/admin/*" element={<AdminDashboard />} />

        {/* Storefront routes wrapped in main site layout */}
        <Route element={<Layout />}>
          <Route path="/" element={<HomePage />} />
          <Route path="/about" element={<AboutPage />} />
          <Route path="/about/" element={<AboutPage />} />
          <Route path="/products" element={<ProductsPage />} />
          <Route path="/products/" element={<ProductsPage />} />
          <Route path="/products/:slug" element={<ProductDetailPage />} />
          <Route path="/products/:slug/" element={<ProductDetailPage />} />
          <Route
            path="/polymer-category/:slug/*"
            element={<ProductsPage taxonomyType="polymer_category" />}
          />
          <Route
            path="/material-grade/:slug/*"
            element={<ProductsPage taxonomyType="material_grade" />}
          />
          <Route
            path="/material-form/:slug/*"
            element={<ProductsPage taxonomyType="material_form" />}
          />
          <Route
            path="/application/:slug/*"
            element={<ProductsPage taxonomyType="application" />}
          />
          <Route path="/polymer-products" element={<Navigate to="/products/" replace />} />
          <Route path="/polymer-products/" element={<Navigate to="/products/" replace />} />
          <Route path="/polymer-products/:slug/*" element={<LegacyProductRedirect />} />
          <Route path="/contact" element={<ContactPage />} />
          <Route path="/contact/" element={<ContactPage />} />
          <Route path="/privacy-policy" element={<PrivacyPolicyPage />} />
          <Route path="/privacy-policy/" element={<PrivacyPolicyPage />} />
          <Route path="/terms-of-use" element={<TermsOfUsePage />} />
          <Route path="/terms-of-use/" element={<TermsOfUsePage />} />
          <Route path="/search" element={<SearchPage />} />
          <Route path="/search/" element={<SearchPage />} />
          <Route path="*" element={<NotFoundPage />} />
        </Route>
      </Routes>
    </>
  );
}

export function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <SiteProvider>
          <AppRoutes />
        </SiteProvider>
      </AuthProvider>
    </BrowserRouter>
  );
}
