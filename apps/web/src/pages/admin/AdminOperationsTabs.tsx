import { useEffect, useState, type FormEvent } from 'react';
import { apiRequest } from '../../lib/api';

const formatRupees = (paise: number) => `₹${(Number(paise || 0) / 100).toFixed(2)}`;

export function AdminOrdersTab({
  canFulfil,
  onNotify,
}: {
  canFulfil: boolean;
  onNotify: (msg: string, isError?: boolean) => void;
}) {
  const [orders, setOrders] = useState<Array<Record<string, unknown>>>([]);
  const [loading, setLoading] = useState(true);
  const [editingOrder, setEditingOrder] = useState<Record<string, unknown> | null>(null);
  const [fulfilmentStatus, setFulfilmentStatus] = useState('pending');
  const [courier, setCourier] = useState('');
  const [tracking, setTracking] = useState('');
  const [notes, setNotes] = useState('');

  const loadOrders = async () => {
    setLoading(true);
    try {
      const res = await apiRequest<{ items: Array<Record<string, unknown>> }>(
        '/api/v1/admin/orders?limit=50'
      );
      setOrders(res.items || []);
    } catch (err) {
      onNotify(err instanceof Error ? err.message : 'Failed to load orders', true);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadOrders();
  }, []);

  const handleFulfilSave = async (e: FormEvent) => {
    e.preventDefault();
    if (!editingOrder) return;
    try {
      await apiRequest(`/api/v1/admin/orders/${editingOrder.id}/fulfil`, {
        method: 'PATCH',
        body: JSON.stringify({
          fulfilment_status: fulfilmentStatus,
          manual_courier: courier,
          manual_tracking: tracking,
          manual_notes: notes,
        }),
      });
      onNotify(`Updated fulfilment for order #${editingOrder.order_number}`);
      setEditingOrder(null);
      await loadOrders();
    } catch (err) {
      onNotify(err instanceof Error ? err.message : 'Could not update order', true);
    }
  };

  const handleShiprocketRetry = async (orderId: string) => {
    try {
      await apiRequest(`/api/v1/admin/orders/${orderId}/shiprocket-retry`, { method: 'POST' });
      onNotify('Shiprocket shipment booking attempted.');
      await loadOrders();
    } catch (err) {
      onNotify(err instanceof Error ? err.message : 'Shiprocket retry failed', true);
    }
  };

  return (
    <div className="rgw-panel">
      <h2 style={{ marginTop: 0 }}>Online Orders &amp; Fulfilment</h2>
      {editingOrder && (
        <form
          onSubmit={handleFulfilSave}
          style={{
            border: '1px solid #dcdcde',
            padding: '14px',
            borderRadius: '8px',
            marginBottom: '16px',
            background: '#fafafa',
          }}
        >
          <h3 style={{ marginTop: 0 }}>Update Order #{String(editingOrder.order_number)}</h3>
          <div className="form-grid form-grid--2" style={{ gap: '10px' }}>
            <div className="form-field">
              <label className="form-label">Fulfilment Status</label>
              <select
                className="form-select"
                value={fulfilmentStatus}
                onChange={(e) => setFulfilmentStatus(e.target.value)}
              >
                <option value="pending">Pending</option>
                <option value="booked">Booked</option>
                <option value="dispatched">Dispatched</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div className="form-field">
              <label className="form-label">Courier Name</label>
              <input
                className="form-input"
                value={courier}
                onChange={(e) => setCourier(e.target.value)}
              />
            </div>
            <div className="form-field">
              <label className="form-label">AWB / Tracking Code</label>
              <input
                className="form-input"
                value={tracking}
                onChange={(e) => setTracking(e.target.value)}
              />
            </div>
            <div className="form-field">
              <label className="form-label">Internal Notes</label>
              <input
                className="form-input"
                value={notes}
                onChange={(e) => setNotes(e.target.value)}
              />
            </div>
          </div>
          <div style={{ marginTop: '10px', display: 'flex', gap: '8px' }}>
            <button type="submit" className="btn btn--primary btn--sm">
              Save Fulfilment
            </button>
            <button
              type="button"
              className="btn btn--secondary btn--sm"
              onClick={() => setEditingOrder(null)}
            >
              Cancel
            </button>
          </div>
        </form>
      )}

      {loading ? (
        <p>Loading orders…</p>
      ) : orders.length === 0 ? (
        <p>No online orders recorded yet.</p>
      ) : (
        <div className="table-wrap">
          <table className="data-table">
            <thead>
              <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Fulfilment</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {orders.map((o) => (
                <tr key={String(o.id)}>
                  <td>
                    <strong>{String(o.order_number)}</strong>
                    <div style={{ fontSize: '0.78rem', color: '#666' }}>
                      {String(o.mode || 'test').toUpperCase()}
                    </div>
                  </td>
                  <td>
                    {String(o.customer_name || '—')}
                    <div style={{ fontSize: '0.8rem', color: '#666' }}>
                      {String(o.shipping_pincode || '')}
                    </div>
                  </td>
                  <td>{formatRupees(Number(o.total_paise || 0))}</td>
                  <td>{String(o.payment_status || 'pending')}</td>
                  <td>
                    {String(o.fulfilment_status || 'pending')}
                    {o.manual_tracking ? ` (${String(o.manual_tracking)})` : ''}
                  </td>
                  <td>
                    {canFulfil && (
                      <div style={{ display: 'flex', gap: '6px' }}>
                        <button
                          type="button"
                          className="btn btn--secondary btn--sm"
                          onClick={() => {
                            setEditingOrder(o);
                            setFulfilmentStatus(String(o.fulfilment_status || 'pending'));
                            setCourier(String(o.manual_courier || ''));
                            setTracking(String(o.manual_tracking || ''));
                            setNotes(String(o.manual_notes || ''));
                          }}
                        >
                          Fulfil
                        </button>
                        <button
                          type="button"
                          className="btn btn--dark btn--sm"
                          onClick={() => handleShiprocketRetry(String(o.id))}
                        >
                          Shiprocket
                        </button>
                      </div>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

export function AdminEnquiriesTab({
  canWrite,
  onNotify,
}: {
  canWrite: boolean;
  onNotify: (msg: string, isError?: boolean) => void;
}) {
  const [enquiries, setEnquiries] = useState<Array<Record<string, unknown>>>([]);
  const [loading, setLoading] = useState(true);

  const loadEnquiries = async () => {
    setLoading(true);
    try {
      const res = await apiRequest<{ items: Array<Record<string, unknown>> }>(
        '/api/v1/admin/enquiries?limit=50'
      );
      setEnquiries(res.items || []);
    } catch (err) {
      onNotify(err instanceof Error ? err.message : 'Failed to load enquiries', true);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadEnquiries();
  }, []);

  const updateStatus = async (id: string, status: string) => {
    try {
      await apiRequest(`/api/v1/admin/enquiries/${id}`, {
        method: 'PATCH',
        body: JSON.stringify({ status }),
      });
      onNotify(`Enquiry marked as ${status}.`);
      await loadEnquiries();
    } catch (err) {
      onNotify(err instanceof Error ? err.message : 'Failed to update enquiry', true);
    }
  };

  return (
    <div className="rgw-panel">
      <h2 style={{ marginTop: 0 }}>B2B RFQ Enquiries</h2>
      {loading ? (
        <p>Loading enquiries…</p>
      ) : enquiries.length === 0 ? (
        <p>No B2B enquiries received yet.</p>
      ) : (
        <div className="table-wrap">
          <table className="data-table">
            <thead>
              <tr>
                <th>Buyer &amp; Company</th>
                <th>Material &amp; Type</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Update</th>
              </tr>
            </thead>
            <tbody>
              {enquiries.map((e) => (
                <tr key={String(e.id)}>
                  <td>
                    <strong>{String(e.full_name)}</strong> — {String(e.company_name)}
                    <div style={{ fontSize: '0.8rem', color: '#666' }}>
                      {String(e.email)} · {String(e.phone)}
                    </div>
                  </td>
                  <td>
                    {String(e.polymer_product)}
                    <div style={{ fontSize: '0.8rem', color: '#666' }}>{String(e.enquiry_type)}</div>
                  </td>
                  <td>{String(e.estimated_quantity || '—')}</td>
                  <td>{String(e.status || 'new')}</td>
                  <td>
                    {canWrite && (
                      <select
                        className="form-select"
                        value={String(e.status || 'new')}
                        onChange={(ev) => updateStatus(String(e.id), ev.target.value)}
                      >
                        <option value="new">New</option>
                        <option value="in_progress">In Progress</option>
                        <option value="quoted">Quoted</option>
                        <option value="closed">Closed</option>
                        <option value="spam">Spam</option>
                      </select>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

export function AdminUsersTab({
  canWrite,
  onNotify,
}: {
  canWrite: boolean;
  onNotify: (msg: string, isError?: boolean) => void;
}) {
  const [users, setUsers] = useState<Array<Record<string, unknown>>>([]);
  const [email, setEmail] = useState('');
  const [fullName, setFullName] = useState('');
  const [password, setPassword] = useState('');
  const [role, setRole] = useState<'manager' | 'staff' | 'administrator'>('staff');

  const loadUsers = async () => {
    try {
      const res = await apiRequest<{ items: Array<Record<string, unknown>> }>('/api/v1/admin/users');
      setUsers(res.items || []);
    } catch (err) {
      onNotify(err instanceof Error ? err.message : 'Failed to load users', true);
    }
  };

  useEffect(() => {
    loadUsers();
  }, []);

  const handleCreate = async (e: FormEvent) => {
    e.preventDefault();
    try {
      await apiRequest('/api/v1/admin/users', {
        method: 'POST',
        body: JSON.stringify({ email, full_name: fullName, password, role }),
      });
      onNotify(`Created ${role} user ${email}.`);
      setEmail('');
      setFullName('');
      setPassword('');
      await loadUsers();
    } catch (err) {
      onNotify(err instanceof Error ? err.message : 'Could not create user', true);
    }
  };

  return (
    <div className="rgw-panel">
      <h2 style={{ marginTop: 0 }}>Team &amp; Role Management</h2>
      {canWrite && (
        <form
          onSubmit={handleCreate}
          className="form-grid form-grid--4"
          style={{ gap: '10px', marginBottom: '20px', alignItems: 'end' }}
        >
          <div className="form-field">
            <label className="form-label">Full Name</label>
            <input
              className="form-input"
              required
              value={fullName}
              onChange={(e) => setFullName(e.target.value)}
            />
          </div>
          <div className="form-field">
            <label className="form-label">Email</label>
            <input
              className="form-input"
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
          </div>
          <div className="form-field">
            <label className="form-label">Password (min 8)</label>
            <input
              className="form-input"
              type="password"
              minLength={8}
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
          </div>
          <div className="form-field">
            <label className="form-label">Role</label>
            <div style={{ display: 'flex', gap: '8px' }}>
              <select
                className="form-select"
                value={role}
                onChange={(e) =>
                  setRole(e.target.value as 'manager' | 'staff' | 'administrator')
                }
              >
                <option value="staff">Staff</option>
                <option value="manager">Manager</option>
                <option value="administrator">Administrator</option>
              </select>
              <button type="submit" className="btn btn--primary btn--sm">
                Add
              </button>
            </div>
          </div>
        </form>
      )}

      <div className="table-wrap">
        <table className="data-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Active</th>
            </tr>
          </thead>
          <tbody>
            {users.map((u) => (
              <tr key={String(u.id)}>
                <td>{String(u.full_name || '—')}</td>
                <td>{String(u.email)}</td>
                <td>{String(u.role)}</td>
                <td>{u.is_active ? 'Yes' : 'No'}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

export function AdminSettingsTab({
  canWrite,
  onNotify,
}: {
  canWrite: boolean;
  onNotify: (msg: string, isError?: boolean) => void;
}) {
  const [settings, setSettings] = useState<Record<string, Record<string, unknown>>>({});
  const [paymentMode, setPaymentMode] = useState<'test' | 'live'>('test');
  const [pickupPin, setPickupPin] = useState('');
  const [companyPhone, setCompanyPhone] = useState('');
  const [companyEmail, setCompanyEmail] = useState('');

  useEffect(() => {
    apiRequest<{ settings: Record<string, Record<string, unknown>> }>('/api/v1/admin/settings')
      .then((res) => {
        setSettings(res.settings || {});
        const pay = (res.settings?.payment_settings || {}) as Record<string, unknown>;
        const ship = (res.settings?.shiprocket_settings || {}) as Record<string, unknown>;
        const comp = (res.settings?.company_info || {}) as Record<string, unknown>;
        setPaymentMode(pay.mode === 'live' ? 'live' : 'test');
        setPickupPin(String(ship.pickup_pincode || ''));
        setCompanyPhone(String(comp.phone || ''));
        setCompanyEmail(String(comp.email || ''));
      })
      .catch((err) =>
        onNotify(err instanceof Error ? err.message : 'Failed to load settings', true)
      );
  }, []);

  const handleSave = async (e: FormEvent) => {
    e.preventDefault();
    try {
      await apiRequest('/api/v1/admin/settings', {
        method: 'PUT',
        body: JSON.stringify({
          payment_settings: { ...(settings.payment_settings || {}), mode: paymentMode },
          shiprocket_settings: {
            ...(settings.shiprocket_settings || {}),
            pickup_pincode: pickupPin,
          },
          company_info: {
            ...(settings.company_info || {}),
            phone: companyPhone,
            email: companyEmail,
          },
        }),
      });
      onNotify('Platform settings saved.');
    } catch (err) {
      onNotify(err instanceof Error ? err.message : 'Could not save settings', true);
    }
  };

  return (
    <div className="rgw-panel">
      <h2 style={{ marginTop: 0 }}>Payment, Shiprocket &amp; Company Settings</h2>
      <form onSubmit={handleSave} className="form-grid form-grid--2" style={{ gap: '12px' }}>
        <div className="form-field">
          <label className="form-label">Razorpay Mode</label>
          <select
            className="form-select"
            value={paymentMode}
            disabled={!canWrite}
            onChange={(e) => setPaymentMode(e.target.value === 'live' ? 'live' : 'test')}
          >
            <option value="test">Test Mode</option>
            <option value="live">Live Mode</option>
          </select>
        </div>
        <div className="form-field">
          <label className="form-label">Shiprocket Pickup Pincode</label>
          <input
            className="form-input"
            value={pickupPin}
            disabled={!canWrite}
            onChange={(e) => setPickupPin(e.target.value)}
          />
        </div>
        <div className="form-field">
          <label className="form-label">Company Sales Phone</label>
          <input
            className="form-input"
            value={companyPhone}
            disabled={!canWrite}
            onChange={(e) => setCompanyPhone(e.target.value)}
          />
        </div>
        <div className="form-field">
          <label className="form-label">Company RFQ Email</label>
          <input
            className="form-input"
            value={companyEmail}
            disabled={!canWrite}
            onChange={(e) => setCompanyEmail(e.target.value)}
          />
        </div>
        {canWrite && (
          <div className="form-field form-field--full">
            <button type="submit" className="btn btn--primary btn--sm">
              Save Settings
            </button>
          </div>
        )}
      </form>
    </div>
  );
}

export function AdminAuditTab({
  onNotify,
}: {
  onNotify: (msg: string, isError?: boolean) => void;
}) {
  const [logs, setLogs] = useState<Array<Record<string, unknown>>>([]);

  useEffect(() => {
    apiRequest<{ items: Array<Record<string, unknown>> }>('/api/v1/admin/audit-logs?limit=50')
      .then((res) => setLogs(res.items || []))
      .catch((err) =>
        onNotify(err instanceof Error ? err.message : 'Failed to load audit log', true)
      );
  }, []);

  return (
    <div className="rgw-panel">
      <h2 style={{ marginTop: 0 }}>Security &amp; Operations Audit Trail</h2>
      <div className="table-wrap">
        <table className="data-table">
          <thead>
            <tr>
              <th>Time</th>
              <th>Action</th>
              <th>Resource</th>
              <th>Actor</th>
            </tr>
          </thead>
          <tbody>
            {logs.map((l) => (
              <tr key={String(l.id)}>
                <td>{new Date(String(l.created_at)).toLocaleString()}</td>
                <td>
                  <code>{String(l.action)}</code>
                </td>
                <td>
                  {String(l.entity_type)} {l.entity_id ? `(${String(l.entity_id).slice(0, 8)})` : ''}
                </td>
                <td>{String(l.actor_email || 'system')}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
