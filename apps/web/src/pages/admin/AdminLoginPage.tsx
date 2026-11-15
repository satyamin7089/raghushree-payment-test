import { useState, type FormEvent } from 'react';
import { Link, Navigate } from 'react-router-dom';
import { useAuth } from '../../components/AuthContext';
import { SeoHead } from '../../lib/seo';

export function AdminLoginPage() {
  const { user, loading, login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');

  if (!loading && user) {
    return <Navigate to="/admin" replace />;
  }

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setSubmitting(true);
    try {
      await login(email.trim(), password);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Login failed.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <>
      <SeoHead
        title="Raghushree Workspace Sign In"
        description="Sign in to Raghushree product, order, enquiry, and payment administration workspace."
        robots="noindex,nofollow"
      />
      <section className="section surface--pearl" style={{ minHeight: '75vh' }}>
        <div className="container" style={{ maxWidth: '460px', paddingBlock: 'var(--space-9)' }}>
          <div className="rgw-panel">
            <span className="rgw-badge rgw-badge--test" style={{ marginBottom: '12px' }}>
              Raghushree Workspace
            </span>
            <h1 style={{ fontSize: '1.6rem', marginBottom: '0.4rem' }}>Staff &amp; Admin Sign In</h1>
            <p className="rgw-subtitle" style={{ marginBottom: '1.5rem' }}>
              Sign in with your administrator, manager, or staff account.
            </p>

            {error && (
              <div className="form-notice form-notice--error" role="alert">
                {error}
              </div>
            )}

            <form onSubmit={handleSubmit}>
              <div className="form-field" style={{ marginBottom: '1rem' }}>
                <label className="form-label" htmlFor="admin-email">
                  Email address
                </label>
                <input
                  id="admin-email"
                  type="email"
                  className="form-input"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="admin@raghushree.com"
                />
              </div>

              <div className="form-field" style={{ marginBottom: '1.5rem' }}>
                <label className="form-label" htmlFor="admin-password">
                  Password
                </label>
                <input
                  id="admin-password"
                  type="password"
                  className="form-input"
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                />
              </div>

              <button
                type="submit"
                className="btn btn--primary btn--full"
                disabled={submitting}
              >
                {submitting ? 'Signing in…' : 'Sign in to Workspace'}
              </button>
            </form>

            <p style={{ marginTop: '1.25rem', fontSize: '0.88rem', textAlign: 'center' }}>
              <Link to="/">← Back to Raghushree Storefront</Link>
            </p>
          </div>
        </div>
      </section>
    </>
  );
}
