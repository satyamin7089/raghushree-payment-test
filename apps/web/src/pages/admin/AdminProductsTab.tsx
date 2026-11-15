import { useEffect, useState, type ChangeEvent, type FormEvent } from 'react';
import { apiRequest } from '../../lib/api';
import type { CategoryDto, ProductDto } from '../../types/api';

const EMPTY_FORM = {
  title: '',
  slug: '',
  polymer_code: '',
  short_desc: '',
  content_html: '',
  primary_category_id: '',
  product_type: 'polymer' as 'polymer' | 'pipe',
  gst_rate: '18',
  hsn: '',
  length_cm: '30',
  width_cm: '25',
  height_cm: '10',
  pack_5_enabled: true,
  pack_5_price: '750',
  pack_10_enabled: true,
  pack_10_price: '1450',
  pack_15_enabled: true,
  pack_15_price: '2150',
  pack_20_enabled: true,
  pack_20_price: '2800',
  pack_25_enabled: true,
  pack_25_price: '3450',
  sample_enabled: false,
  sample_weight_kg: '0.5',
  packet_price: '500',
  packet_label: 'One quantity is one packet.',
  packet_rows_text: '1, 1, 30, 25, 10',
  featured_media_id: null as string | null,
  featured_image_url: '',
  packaging: '',
  moq: '',
  availability: '',
  status: 'published' as 'published' | 'draft' | 'archived',
};

export function AdminProductsTab({
  categories,
  canWrite,
  canDelete,
  onNotify,
}: {
  categories: CategoryDto[];
  canWrite: boolean;
  canDelete: boolean;
  onNotify: (msg: string, isError?: boolean) => void;
}) {
  const [products, setProducts] = useState<ProductDto[]>([]);
  const [loading, setLoading] = useState(true);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [showEditor, setShowEditor] = useState(false);
  const [form, setForm] = useState({ ...EMPTY_FORM });
  const [saving, setSaving] = useState(false);
  const [uploading, setUploading] = useState(false);

  const loadProducts = async () => {
    setLoading(true);
    try {
      const res = await apiRequest<{ items: ProductDto[] }>('/api/v1/admin/products?limit=100');
      setProducts(res.items || []);
    } catch (err) {
      onNotify(err instanceof Error ? err.message : 'Failed to load products', true);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadProducts();
  }, []);

  const openCreate = () => {
    setEditingId(null);
    setForm({
      ...EMPTY_FORM,
      primary_category_id: categories[0]?.id || '',
    });
    setShowEditor(true);
  };

  const openEdit = (p: ProductDto) => {
    setEditingId(p.id);
    const mc = p.master_config;
    const vars = p.variations || {};
    const firstVar = vars['5kg'] || vars['packet_1'] || Object.values(vars)[0];
    const pipeRows = Object.values(vars)
      .filter((v) => v.variation_kind === 'pipe_packet')
      .sort((a, b) => a.quantity - b.quantity)
      .map(
        (v) =>
          `${v.quantity}, ${v.weight_kg || '1'}, ${v.length_cm || '30'}, ${v.width_cm || '25'}, ${
            v.height_cm || '10'
          }`
      )
      .join('\n');

    setForm({
      title: p.title,
      slug: p.slug,
      polymer_code: p.polymer_code || '',
      short_desc: p.short_desc || '',
      content_html: p.content_html || '',
      primary_category_id: p.primary_category?.id || categories[0]?.id || '',
      product_type: mc.product_type || 'polymer',
      gst_rate: String((mc.gst_bps || 1800) / 100),
      hsn: mc.hsn || '',
      length_cm: firstVar?.length_cm || '30',
      width_cm: firstVar?.width_cm || '25',
      height_cm: firstVar?.height_cm || '10',
      pack_5_enabled: Boolean(vars['5kg']?.enabled),
      pack_5_price: String((vars['5kg']?.price_paise || 0) / 100),
      pack_10_enabled: Boolean(vars['10kg']?.enabled),
      pack_10_price: String((vars['10kg']?.price_paise || 0) / 100),
      pack_15_enabled: Boolean(vars['15kg']?.enabled),
      pack_15_price: String((vars['15kg']?.price_paise || 0) / 100),
      pack_20_enabled: Boolean(vars['20kg']?.enabled),
      pack_20_price: String((vars['20kg']?.price_paise || 0) / 100),
      pack_25_enabled: Boolean(vars['25kg']?.enabled),
      pack_25_price: String((vars['25kg']?.price_paise || 0) / 100),
      sample_enabled: Boolean(vars['sample']?.enabled),
      sample_weight_kg: vars['sample']?.weight_kg || '0.5',
      packet_price: String((mc.packet_price_paise || 0) / 100),
      packet_label: mc.packet_label || 'One quantity is one packet.',
      packet_rows_text: pipeRows || '1, 1, 30, 25, 10',
      featured_media_id: null,
      featured_image_url: p.featured_image_url || '',
      packaging: p.packaging || '',
      moq: p.moq || '',
      availability: p.availability || '',
      status: p.status,
    });
    setShowEditor(true);
  };

  const handleImageUpload = async (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    setUploading(true);
    try {
      const fd = new FormData();
      fd.append('file', file);
      const res = await apiRequest<{ id: string; url: string }>('/api/v1/admin/media/upload', {
        method: 'POST',
        body: fd,
      });
      setForm((prev) => ({
        ...prev,
        featured_media_id: res.id,
        featured_image_url: res.url,
      }));
      onNotify('Image uploaded and attached.');
    } catch (err) {
      onNotify(err instanceof Error ? err.message : 'Image upload failed', true);
    } finally {
      setUploading(false);
    }
  };

  const handleSave = async (e: FormEvent) => {
    e.preventDefault();
    setSaving(true);
    const productPayload = {
      title: form.title.trim(),
      slug: form.slug.trim() || undefined,
      polymer_code: form.polymer_code.trim(),
      short_desc: form.short_desc.trim(),
      content_html: form.content_html,
      primary_category_id: form.primary_category_id || null,
      ...(form.featured_media_id ? { featured_media_id: form.featured_media_id } : {}),
      packaging: form.packaging.trim(),
      moq: form.moq.trim(),
      availability: form.availability.trim(),
      status: form.status,
    };

    const masterPayload = {
      product_type: form.product_type,
      gst_percent: form.gst_rate,
      hsn: form.hsn.trim(),
      polymer_packs: {
        '5kg': {
          enabled: form.pack_5_enabled,
          price_rupees: form.pack_5_price,
          weight_kg: '5',
          length_cm: form.length_cm,
          width_cm: form.width_cm,
          height_cm: form.height_cm,
        },
        '10kg': {
          enabled: form.pack_10_enabled,
          price_rupees: form.pack_10_price,
          weight_kg: '10',
          length_cm: form.length_cm,
          width_cm: form.width_cm,
          height_cm: form.height_cm,
        },
        '15kg': {
          enabled: form.pack_15_enabled,
          price_rupees: form.pack_15_price,
          weight_kg: '15',
          length_cm: form.length_cm,
          width_cm: form.width_cm,
          height_cm: form.height_cm,
        },
        '20kg': {
          enabled: form.pack_20_enabled,
          price_rupees: form.pack_20_price,
          weight_kg: '20',
          length_cm: form.length_cm,
          width_cm: form.width_cm,
          height_cm: form.height_cm,
        },
        '25kg': {
          enabled: form.pack_25_enabled,
          price_rupees: form.pack_25_price,
          weight_kg: '25',
          length_cm: form.length_cm,
          width_cm: form.width_cm,
          height_cm: form.height_cm,
        },
        sample: {
          enabled: form.sample_enabled,
          price_rupees: '0',
          weight_kg: form.sample_weight_kg || '0.5',
          length_cm: form.length_cm,
          width_cm: form.width_cm,
          height_cm: form.height_cm,
        },
      },
      packet_price_rupees: form.packet_price,
      packet_label: form.packet_label.trim(),
      packet_rows_text: form.packet_rows_text,
    };

    try {
      let savedId = editingId;
      if (editingId) {
        await apiRequest(`/api/v1/admin/products/${editingId}`, {
          method: 'PUT',
          body: JSON.stringify(productPayload),
        });
      } else {
        const created = await apiRequest<ProductDto>('/api/v1/admin/products', {
          method: 'POST',
          body: JSON.stringify(productPayload),
        });
        savedId = created.id;
      }
      if (savedId) {
        await apiRequest(`/api/v1/admin/products/${savedId}/master`, {
          method: 'PUT',
          body: JSON.stringify(masterPayload),
        });
      }
      onNotify(editingId ? 'Product updated successfully.' : 'Product created successfully.');
      setShowEditor(false);
      await loadProducts();
    } catch (err) {
      onNotify(err instanceof Error ? err.message : 'Could not save product', true);
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (id: string, title: string) => {
    if (!window.confirm(`Delete product "${title}"?`)) return;
    try {
      await apiRequest(`/api/v1/admin/products/${id}`, { method: 'DELETE' });
      onNotify(`Deleted "${title}".`);
      await loadProducts();
    } catch (err) {
      onNotify(err instanceof Error ? err.message : 'Delete failed', true);
    }
  };

  return (
    <div className="rgw-panel">
      <div
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          marginBottom: '16px',
          gap: '12px',
          flexWrap: 'wrap',
        }}
      >
        <div>
          <h2 style={{ margin: 0 }}>Products &amp; Product Master Pricing</h2>
          <p className="rgw-subtitle">
            Manage polymer grades, industrial pipes, pack pricing, GST rates, and shipping
            dimensions.
          </p>
        </div>
        {canWrite && (
          <button type="button" className="btn btn--primary btn--sm" onClick={openCreate}>
            + Add Product
          </button>
        )}
      </div>

      {showEditor && (
        <form
          onSubmit={handleSave}
          style={{
            border: '1px solid #dcdcde',
            borderRadius: '8px',
            padding: '16px',
            marginBottom: '20px',
            background: '#fafafa',
          }}
        >
          <h3 style={{ marginTop: 0 }}>{editingId ? 'Edit Product' : 'New Product'}</h3>
          <div className="form-grid form-grid--2" style={{ gap: '12px' }}>
            <div className="form-field">
              <label className="form-label">Title</label>
              <input
                className="form-input"
                required
                value={form.title}
                onChange={(e) => setForm({ ...form, title: e.target.value })}
              />
            </div>
            <div className="form-field">
              <label className="form-label">Slug</label>
              <input
                className="form-input"
                value={form.slug}
                placeholder="auto-generated-from-title"
                onChange={(e) => setForm({ ...form, slug: e.target.value })}
              />
            </div>
            <div className="form-field">
              <label className="form-label">Primary Category</label>
              <select
                className="form-select"
                value={form.primary_category_id}
                onChange={(e) => setForm({ ...form, primary_category_id: e.target.value })}
              >
                <option value="">Uncategorized</option>
                {categories.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.title}
                  </option>
                ))}
              </select>
            </div>
            <div className="form-field">
              <label className="form-label">Product Type</label>
              <select
                className="form-select"
                value={form.product_type}
                onChange={(e) =>
                  setForm({
                    ...form,
                    product_type: e.target.value === 'pipe' ? 'pipe' : 'polymer',
                  })
                }
              >
                <option value="polymer">Polymer (5–25 kg packs)</option>
                <option value="pipe">Pipe (Packet stepper up to 30 kg)</option>
              </select>
            </div>
            <div className="form-field">
              <label className="form-label">Polymer / Product Code</label>
              <input
                className="form-input"
                value={form.polymer_code}
                onChange={(e) => setForm({ ...form, polymer_code: e.target.value })}
              />
            </div>
            <div className="form-field">
              <label className="form-label">GST Rate (%)</label>
              <input
                className="form-input"
                type="number"
                step="0.01"
                value={form.gst_rate}
                onChange={(e) => setForm({ ...form, gst_rate: e.target.value })}
              />
            </div>
            <div className="form-field">
              <label className="form-label">HSN Code</label>
              <input
                className="form-input"
                value={form.hsn}
                onChange={(e) => setForm({ ...form, hsn: e.target.value })}
              />
            </div>
            <div className="form-field">
              <label className="form-label">Status</label>
              <select
                className="form-select"
                value={form.status}
                onChange={(e) =>
                  setForm({
                    ...form,
                    status:
                      e.target.value === 'draft'
                        ? 'draft'
                        : e.target.value === 'archived'
                        ? 'archived'
                        : 'published',
                  })
                }
              >
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
              </select>
            </div>
            <div className="form-field form-field--full">
              <label className="form-label">Short Description</label>
              <textarea
                className="form-textarea"
                rows={2}
                value={form.short_desc}
                onChange={(e) => setForm({ ...form, short_desc: e.target.value })}
              />
            </div>
            <div className="form-field form-field--full">
              <label className="form-label">Featured Image Upload</label>
              <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap', alignItems: 'center' }}>
                <input type="file" accept="image/*" onChange={handleImageUpload} disabled={uploading} />
                {form.featured_image_url && (
                  <small style={{ color: '#555' }}>Current: {form.featured_image_url}</small>
                )}
              </div>
            </div>
          </div>

          {form.product_type === 'polymer' ? (
            <div style={{ marginTop: '14px' }}>
              <strong>Polymer Pack Sizes (INR before GST)</strong>
              <div className="form-grid form-grid--3" style={{ gap: '8px', marginTop: '8px' }}>
                {(
                  [
                    ['5 kg', 'pack_5_enabled', 'pack_5_price'],
                    ['10 kg', 'pack_10_enabled', 'pack_10_price'],
                    ['15 kg', 'pack_15_enabled', 'pack_15_price'],
                    ['20 kg', 'pack_20_enabled', 'pack_20_price'],
                    ['25 kg', 'pack_25_enabled', 'pack_25_price'],
                  ] as const
                ).map(([lbl, enKey, prKey]) => (
                  <div key={lbl} className="form-field">
                    <label>
                      <input
                        type="checkbox"
                        checked={Boolean(form[enKey])}
                        onChange={(e) => setForm({ ...form, [enKey]: e.target.checked })}
                      />{' '}
                      {lbl} (₹)
                    </label>
                    <input
                      className="form-input"
                      type="number"
                      step="0.01"
                      value={form[prKey]}
                      onChange={(e) => setForm({ ...form, [prKey]: e.target.value })}
                    />
                  </div>
                ))}
                <div className="form-field">
                  <label>
                    <input
                      type="checkbox"
                      checked={form.sample_enabled}
                      onChange={(e) => setForm({ ...form, sample_enabled: e.target.checked })}
                    />{' '}
                    Free Sample (kg)
                  </label>
                  <input
                    className="form-input"
                    type="number"
                    step="0.1"
                    value={form.sample_weight_kg}
                    onChange={(e) => setForm({ ...form, sample_weight_kg: e.target.value })}
                  />
                </div>
              </div>
            </div>
          ) : (
            <div style={{ marginTop: '14px' }} className="form-grid form-grid--2">
              <div className="form-field">
                <label className="form-label">Packet Price (₹)</label>
                <input
                  className="form-input"
                  type="number"
                  step="0.01"
                  value={form.packet_price}
                  onChange={(e) => setForm({ ...form, packet_price: e.target.value })}
                />
              </div>
              <div className="form-field">
                <label className="form-label">Packet Note</label>
                <input
                  className="form-input"
                  value={form.packet_label}
                  onChange={(e) => setForm({ ...form, packet_label: e.target.value })}
                />
              </div>
              <div className="form-field form-field--full">
                <label className="form-label">
                  Packet Dimensions CSV (qty, weight_kg, length_cm, width_cm, height_cm)
                </label>
                <textarea
                  className="form-textarea"
                  rows={3}
                  value={form.packet_rows_text}
                  onChange={(e) => setForm({ ...form, packet_rows_text: e.target.value })}
                />
              </div>
            </div>
          )}

          <div style={{ marginTop: '14px', display: 'flex', gap: '8px' }}>
            <button type="submit" className="btn btn--primary btn--sm" disabled={saving}>
              {saving ? 'Saving…' : 'Save Product'}
            </button>
            <button
              type="button"
              className="btn btn--secondary btn--sm"
              onClick={() => setShowEditor(false)}
            >
              Cancel
            </button>
          </div>
        </form>
      )}

      {loading ? (
        <p>Loading products…</p>
      ) : (
        <div className="table-wrap">
          <table className="data-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Type</th>
                <th>GST</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {products.map((p) => (
                <tr key={p.id}>
                  <td>
                    <strong>{p.title}</strong>
                    <div style={{ fontSize: '0.8rem', color: '#666' }}>/{p.slug}/</div>
                  </td>
                  <td>{p.primary_category?.title || '—'}</td>
                  <td>{p.master_config.product_type}</td>
                  <td>{p.master_config.gst_bps / 100}%</td>
                  <td>
                    <span
                      className={`rgw-badge ${
                        p.status === 'published' ? 'rgw-badge--live' : 'rgw-badge--test'
                      }`}
                    >
                      {p.status}
                    </span>
                  </td>
                  <td>
                    <div style={{ display: 'flex', gap: '8px' }}>
                      {canWrite && (
                        <button
                          type="button"
                          className="btn btn--secondary btn--sm"
                          onClick={() => openEdit(p)}
                        >
                          Edit
                        </button>
                      )}
                      {canDelete && (
                        <button
                          type="button"
                          className="btn btn--dark btn--sm"
                          onClick={() => handleDelete(p.id, p.title)}
                        >
                          Delete
                        </button>
                      )}
                    </div>
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
