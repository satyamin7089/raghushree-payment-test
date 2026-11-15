import { useEffect, useState } from 'react';
import { Link, Navigate } from 'react-router-dom';
import { useAuth } from '../../components/AuthContext';
import { useSite } from '../../components/SiteContext';
import { apiRequest } from '../../lib/api';
import { SeoHead } from '../../lib/seo';
import {
  AdminAuditTab,
  AdminEnquiriesTab,
  AdminOrdersTab,
  AdminSettingsTab,
  AdminUsersTab,
} from './AdminOperationsTabs';
import { AdminProductsTab } from './AdminProductsTab';

type TabKey = 'overview' | 'products' | 'orders' | 'enquiries' | 'users' | 'settings' | 'audit';

export function AdminDashboard() {
  const { user, loading, logout, hasPermission } = useAuth();
  const { config } = useSite();
  const [activeTab, setActiveTab] = useState<TabKey>('overview');
  const [notice, setNotice] = useState<{ msg: string; isError: boolean } | null>(null);
  const [overview, setOverview] = useState<Record<string, number> | null>(null);

  useEffect(() => {
    if (user && hasPermission('orders:read')) {
      apiRequest<Record<string, number>>('/api/v1/admin/overview')
        .then(setOverview)
        .catch(() => {});
    }
  }, [user]);

  if (loading) {
    return (
      <section className="section surface--pearl">
        <div className="container" style={{ paddingBlock: 'var(--space-10)' }}>
          <p>Loading Raghushree workspace…</p>
        </div>
      </section>
    );
  }

  if (!user) {
    return <Navigate to="/admin/login" replace />;
  }

  const notify = (msg: string, isError = false) => {
    setNotice({ msg, isError });
  };

  const allTabs: Array<{ key: TabKey; label: string; perm: string }> = [
    { key: 'overview', label: 'Overview', perm: 'orders:read' },
    { key: 'products', label: 'Products', perm: 'products:read' },
    { key: 'orders', label: 'Orders', perm: 'orders:read' },
    { key: 'enquiries', label: 'Enquiries', perm: 'enquiries:read' },
    { key: 'users', label: 'Users & Roles', perm: 'users:read' },
    { key: 'settings', label: 'Settings', perm: 'settings:read' },
    { key: 'audit', label: 'Audit Log', perm: 'audit:read' },
  ];
  const tabs = allTabs.filter((t) => hasPermission(t.perm));

  return (
    <>
      <SeoHead
        title="Raghushree Workspace | Admin & Operations"
        description="Manage Raghushree catalog, Product Master pricing, Shiprocket quotes, Razorpay orders, and B2B enquiries."
        robots="noindex,nofollow"
      />
      <link rel="stylesheet" href="/assets/css/admin-workspace.css" />

      <div className="rgw-wrap">
        <div className="rgw-header">
          <div>
            <h1>Raghushree Workspace</h1>
            <p className="rgw-subtitle">
              Signed in as <strong>{user.fullName || user.email}</strong> ({user.role}) ·{' '}
              <Link to="/">View Public Storefront</Link>
            </p>
          </div>
          <div style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
            <span
              className={`rgw-badge ${
                config?.payment_mode === 'live' ? 'rgw-badge--live' : 'rgw-badge--test'
              }`}
            >
              {config?.payment_mode === 'live' ? 'LIVE MODE' : 'TEST MODE'}
            </span>
            <button type="button" className="btn btn--secondary btn--sm" onClick={logout}>
              Sign out
            </button>
          </div>
        </div>

        <nav className="rgw-nav" aria-label="Workspace sections">
          {tabs.map((tab) => (
            <button
              key={tab.key}
              type="button"
              className={activeTab === tab.key ? 'is-active' : ''}
              onClick={() => {
                setNotice(null);
                setActiveTab(tab.key);
              }}
              style={{ cursor: 'pointer' }}
            >
              {tab.label}
            </button>
          ))}
        </nav>

        {notice && (
          <div
            className={`form-notice ${
              notice.isError ? 'form-notice--error' : 'form-notice--success'
            }`}
            role="status"
            style={{ marginBottom: '16px' }}
          >
            {notice.msg}
          </div>
        )}

        {activeTab === 'overview' && (
          <div className="rgw-panel">
            <h2 style={{ marginTop: 0 }}>Operations Summary</h2>
            <div className="grid-4">
              <div className="stat-card">
                <span className="stat-card__label">Published Products</span>
                <div className="stat-card__value">{overview?.published_products ?? '—'}</div>
              </div>
              <div className="stat-card">
                <span className="stat-card__label">Total Orders</span>
                <div className="stat-card__value">{overview?.total_orders ?? '—'}</div>
              </div>
              <div className="stat-card">
                <span className="stat-card__label">Paid Orders</span>
                <div className="stat-card__value">{overview?.paid_orders ?? '—'}</div>
              </div>
              <div className="stat-card">
                <span className="stat-card__label">Open Enquiries</span>
                <div className="stat-card__value">{overview?.open_enquiries ?? '—'}</div>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'products' && (
          <AdminProductsTab
            categories={config?.categories || []}
            canWrite={hasPermission('products:write')}
            canDelete={hasPermission('products:delete')}
            onNotify={notify}
          />
        )}

        {activeTab === 'orders' && (
          <AdminOrdersTab canFulfil={hasPermission('orders:fulfil')} onNotify={notify} />
        )}

        {activeTab === 'enquiries' && (
          <AdminEnquiriesTab canWrite={hasPermission('enquiries:write')} onNotify={notify} />
        )}

        {activeTab === 'users' && (
          <AdminUsersTab canWrite={hasPermission('users:write')} onNotify={notify} />
        )}

        {activeTab === 'settings' && (
          <AdminSettingsTab canWrite={hasPermission('settings:write')} onNotify={notify} />
        )}

        {activeTab === 'audit' && <AdminAuditTab onNotify={notify} />}
      </div>
    </>
  );
}
