import { useEffect, useMemo, useRef, useState, type FormEvent } from 'react';
import { Link } from 'react-router-dom';
import { apiRequest } from '../lib/api';
import type { ProductDto, ShippingQuoteDto } from '../types/api';
import { useSite } from './SiteContext';

declare global {
  interface Window {
    Razorpay?: new (options: Record<string, unknown>) => {
      open: () => void;
      on: (event: string, cb: (res: unknown) => void) => void;
    };
  }
}

const formatPaise = (paise: number) => `₹${(Number(paise) / 100).toFixed(2)}`;

export function ProductPurchaseHero({
  product,
  enquireUrl,
  onQuoteReady,
  onQuoteInvalidate,
  locked,
}: {
  product: ProductDto;
  enquireUrl: string;
  onQuoteReady: (quote: ShippingQuoteDto) => void;
  onQuoteInvalidate: () => void;
  locked: boolean;
}) {
  const { config } = useSite();
  const availableEntries = useMemo(
    () => Object.entries(product.variations || {}).filter(([, v]) => v.enabled),
    [product.variations]
  );
  const isPipe = product.master_config.product_type === 'pipe';
  const hasSample = Boolean(product.variations?.sample?.enabled);

  const [selectedOption, setSelectedOption] = useState<string>(
    isPipe ? 'packet_1' : availableEntries[0]?.[0] || ''
  );
  const [packetQty, setPacketQty] = useState(1);
  const [useSample, setUseSample] = useState(false);
  const [pincode, setPincode] = useState('');
  const [checking, setChecking] = useState(false);
  const [quoteStatus, setQuoteStatus] = useState('');

  useEffect(() => {
    setSelectedOption(isPipe ? 'packet_1' : availableEntries[0]?.[0] || '');
    setPacketQty(1);
    setUseSample(false);
    setQuoteStatus('');
  }, [product.id, isPipe, availableEntries]);

  const activeKey = isPipe ? (useSample ? 'sample' : `packet_${packetQty}`) : selectedOption;
  const activeVariation = product.variations?.[activeKey];
  const isVariationValid = Boolean(activeVariation && activeVariation.enabled);

  const handleInvalidate = () => {
    setQuoteStatus('');
    onQuoteInvalidate();
  };

  const handleQuoteSubmit = async (e: FormEvent) => {
    e.preventDefault();
    if (!isVariationValid) {
      setQuoteStatus('Please contact us for this quantity.');
      return;
    }
    if (!/^[1-9][0-9]{5}$/.test(pincode.trim())) {
      setQuoteStatus('Enter a valid 6-digit Indian PIN code.');
      return;
    }

    onQuoteInvalidate();
    setChecking(true);
    setQuoteStatus('Checking shipping…');
    try {
      const quote = await apiRequest<ShippingQuoteDto>('/api/v1/shipping/quote', {
        method: 'POST',
        body: JSON.stringify({
          product_id: product.id,
          variation: activeKey,
          pincode: pincode.trim(),
        }),
      });
      const checkoutHint = config?.checkout_ready
        ? ' Continue below.'
        : ' Checkout is not currently available.';
      setQuoteStatus(
        `Estimated total ${formatPaise(quote.total_paise)} including GST and shipping${
          quote.courier_name ? ` (${quote.courier_name})` : ''
        }.${checkoutHint}`
      );
      onQuoteReady(quote);
    } catch (err) {
      setQuoteStatus(err instanceof Error ? err.message : 'Shipping rates are temporarily unavailable.');
    } finally {
      setChecking(false);
    }
  };

  return (
    <div className="product-purchase-hero" id="product-purchase">
      <p className="purchase-eyebrow">Order online</p>
      {availableEntries.length > 0 ? (
        <form className="product-quote" onSubmit={handleQuoteSubmit}>
          <div className="purchase-price">
            <span>Product price</span>
            <strong data-purchase-price>
              {isVariationValid ? formatPaise(activeVariation.price_paise) : 'Contact us'}
            </strong>
            <small>before GST and shipping</small>
          </div>

          {isPipe ? (
            <>
              <label htmlFor="packet-quantity">Number of packets</label>
              <div className="packet-stepper">
                <button
                  type="button"
                  aria-label="Decrease packet quantity"
                  disabled={locked || useSample}
                  onClick={() => {
                    setPacketQty((q) => Math.max(1, q - 1));
                    handleInvalidate();
                  }}
                >
                  −
                </button>
                <input
                  id="packet-quantity"
                  name="packet_quantity"
                  type="number"
                  min={1}
                  max={999}
                  value={packetQty}
                  disabled={locked || useSample}
                  inputMode="numeric"
                  onChange={(e) => {
                    const n = Math.max(1, Math.min(999, parseInt(e.target.value, 10) || 1));
                    setPacketQty(n);
                    handleInvalidate();
                  }}
                />
                <button
                  type="button"
                  aria-label="Increase packet quantity"
                  disabled={locked || useSample}
                  onClick={() => {
                    setPacketQty((q) => Math.min(999, q + 1));
                    handleInvalidate();
                  }}
                >
                  +
                </button>
              </div>
              {hasSample && (
                <label className="purchase-sample">
                  <input
                    type="checkbox"
                    checked={useSample}
                    disabled={locked}
                    onChange={(e) => {
                      setUseSample(e.target.checked);
                      handleInvalidate();
                    }}
                  />{' '}
                  Order free sample instead
                </label>
              )}
              <small>{product.master_config.packet_label || 'One quantity is one packet.'}</small>
            </>
          ) : (
            <>
              <label htmlFor="quote-pack">Choose pack size</label>
              <select
                id="quote-pack"
                name="option"
                value={selectedOption}
                disabled={locked}
                required
                onChange={(e) => {
                  setSelectedOption(e.target.value);
                  handleInvalidate();
                }}
              >
                {availableEntries.map(([key, pack]) => (
                  <option key={key} value={key}>
                    {pack.label}
                  </option>
                ))}
              </select>
            </>
          )}

          <p className="purchase-quantity-note">
            {!isVariationValid
              ? 'This packet quantity is not configured for online purchase. Please contact us.'
              : isPipe
              ? 'Over 30 kg? Contact us for a custom quote.'
              : '30 kg or more? Contact us for a custom quote.'}
          </p>

          <div className="purchase-actions">
            <a className="btn btn--primary" href="#product-delivery">
              Buy now
            </a>
            <Link className="btn btn--dark" to={enquireUrl}>
              Enquire now
            </Link>
          </div>

          <div id="product-delivery" className="purchase-delivery">
            <label htmlFor="quote-pincode">Check delivery</label>
            <div className="purchase-delivery__controls">
              <input
                id="quote-pincode"
                name="pincode"
                type="text"
                inputMode="numeric"
                pattern="[1-9][0-9]{5}"
                maxLength={6}
                placeholder="Enter delivery PIN"
                value={pincode}
                disabled={locked}
                required
                onChange={(e) => {
                  setPincode(e.target.value.replace(/[^0-9]/g, '').slice(0, 6));
                  handleInvalidate();
                }}
              />
              <button type="submit" disabled={checking || locked}>
                {checking ? 'Checking…' : 'Check'}
              </button>
            </div>
            <p className="product-quote__result" role="status" aria-live="polite">
              {quoteStatus}
            </p>
          </div>
        </form>
      ) : (
        <>
          <p>Pricing is being confirmed. Please contact us for a quote.</p>
          <Link className="btn btn--dark" to={enquireUrl}>
            Enquire now
          </Link>
        </>
      )}
    </div>
  );
}

export function ProductCheckoutSection({
  quote,
  onCheckoutStarted,
}: {
  quote: ShippingQuoteDto | null;
  onCheckoutStarted: () => void;
}) {
  const { config } = useSite();
  const sectionRef = useRef<HTMLElement | null>(null);

  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [company, setCompany] = useState('');
  const [gstin, setGstin] = useState('');
  const [address, setAddress] = useState('');
  const [city, setCity] = useState('');
  const [state, setState] = useState('');

  const [submitting, setSubmitting] = useState(false);
  const [status, setStatus] = useState('');
  const [startedOrder, setStartedOrder] = useState<{
    order_id: string;
    order_number: string;
    razorpay_order_id: string;
    razorpay_key_id: string;
    amount_paise: number;
    product_title: string;
    option_label: string;
    prefill: { name: string; email: string; contact: string };
  } | null>(null);
  const [finished, setFinished] = useState(false);

  useEffect(() => {
    if (quote && sectionRef.current) {
      sectionRef.current.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }, [quote?.quote_token]);

  if (!quote || !config?.checkout_ready) return null;

  const openRazorpay = (orderData: NonNullable<typeof startedOrder>) => {
    if (typeof window.Razorpay !== 'function') {
      setStatus('Razorpay Checkout could not load. Please try again later.');
      setSubmitting(false);
      return;
    }

    const rzp = new window.Razorpay({
      key: orderData.razorpay_key_id,
      order_id: orderData.razorpay_order_id,
      amount: orderData.amount_paise,
      currency: 'INR',
      name: 'Raghushree',
      description: `${orderData.product_title} — ${orderData.option_label}`,
      prefill: orderData.prefill,
      handler: async (response: {
        razorpay_payment_id: string;
        razorpay_order_id: string;
        razorpay_signature: string;
      }) => {
        setFinished(true);
        setSubmitting(true);
        setStatus('Verifying payment…');
        try {
          const verified = await apiRequest<{ order_number: string }>('/api/v1/checkout/verify', {
            method: 'POST',
            body: JSON.stringify({
              order_id: orderData.order_id,
              razorpay_order_id: response.razorpay_order_id,
              razorpay_payment_id: response.razorpay_payment_id,
              razorpay_signature: response.razorpay_signature,
            }),
          });
          setStatus(`Payment confirmed! Order #${verified.order_number}`);
        } catch {
          setStatus(
            `Payment confirmation is pending. Do not pay again. Contact us with Order #${orderData.order_number}.`
          );
        }
      },
      modal: {
        ondismiss: () => {
          setSubmitting(false);
          setStatus(
            `Checkout closed. Resume the same order, or contact us if payment was already made. Order #${orderData.order_number}`
          );
        },
      },
    });

    rzp.on('payment.failed', () => {
      setStatus(
        `Payment failed or is pending. Check your payment status before retrying. Order #${orderData.order_number}`
      );
      setSubmitting(false);
    });

    setStatus('Opening secure payment…');
    rzp.open();
  };

  const handleCheckoutSubmit = async (e: FormEvent) => {
    e.preventDefault();
    if (finished) return;
    if (startedOrder) {
      openRazorpay(startedOrder);
      return;
    }

    setSubmitting(true);
    setStatus('Preparing your order…');
    try {
      const order = await apiRequest<NonNullable<typeof startedOrder>>('/api/v1/checkout/start', {
        method: 'POST',
        body: JSON.stringify({
          product_id: quote.product_id,
          variation: quote.variation,
          quote_token: quote.quote_token,
          customer: {
            name: `${firstName.trim()} ${lastName.trim()}`.trim(),
            email: email.trim(),
            phone: phone.trim(),
            company: company.trim(),
            gstin: gstin.trim(),
            address: address.trim(),
            city: city.trim(),
            state: state.trim(),
            pincode: quote.pincode,
          },
        }),
      });
      setStartedOrder(order);
      onCheckoutStarted();
      openRazorpay(order);
    } catch (err) {
      setSubmitting(false);
      setStatus(
        err instanceof Error
          ? `${err.message} Please check shipping again before retrying.`
          : 'Could not start checkout.'
      );
    }
  };

  return (
    <section ref={sectionRef} className="section surface--pearl checkout-section" id="checkout">
      <div className="container">
        <form className="product-checkout" onSubmit={handleCheckoutSubmit}>
          <div className="checkout-heading">
            <span className="eyebrow">Secure checkout</span>
            <h2>Complete your order</h2>
            <p>Your delivery details and a clear breakdown, all in one place.</p>
          </div>

          <div className="checkout-grid">
            <div className="checkout-details">
              <h3>Delivery details</h3>
              {config.payment_mode === 'test' && (
                <p>
                  <strong>
                    Test mode: no real money will be charged and no shipment will be booked.
                  </strong>
                </p>
              )}
              <div className="product-checkout__fields">
                <label>
                  First name
                  <input
                    name="first_name"
                    required
                    maxLength={50}
                    value={firstName}
                    disabled={Boolean(startedOrder)}
                    onChange={(e) => setFirstName(e.target.value)}
                  />
                </label>
                <label>
                  Last name
                  <input
                    name="last_name"
                    required
                    maxLength={50}
                    value={lastName}
                    disabled={Boolean(startedOrder)}
                    onChange={(e) => setLastName(e.target.value)}
                  />
                </label>
                <label>
                  Email
                  <input
                    name="email"
                    type="email"
                    required
                    maxLength={100}
                    value={email}
                    disabled={Boolean(startedOrder)}
                    onChange={(e) => setEmail(e.target.value)}
                  />
                </label>
                <label>
                  Mobile number
                  <input
                    name="phone"
                    type="tel"
                    inputMode="numeric"
                    pattern="[6-9][0-9]{9}"
                    maxLength={10}
                    required
                    value={phone}
                    disabled={Boolean(startedOrder)}
                    onChange={(e) => setPhone(e.target.value.replace(/[^0-9]/g, '').slice(0, 10))}
                  />
                </label>
                <label>
                  Company (optional)
                  <input
                    name="company"
                    maxLength={100}
                    value={company}
                    disabled={Boolean(startedOrder)}
                    onChange={(e) => setCompany(e.target.value)}
                  />
                </label>
                <label>
                  GSTIN (optional)
                  <input
                    name="gstin"
                    maxLength={15}
                    value={gstin}
                    disabled={Boolean(startedOrder)}
                    onChange={(e) => setGstin(e.target.value.toUpperCase())}
                  />
                </label>
                <label>
                  Address
                  <input
                    name="address"
                    required
                    maxLength={150}
                    value={address}
                    disabled={Boolean(startedOrder)}
                    onChange={(e) => setAddress(e.target.value)}
                  />
                </label>
                <label>
                  City
                  <input
                    name="city"
                    required
                    maxLength={40}
                    value={city}
                    disabled={Boolean(startedOrder)}
                    onChange={(e) => setCity(e.target.value)}
                  />
                </label>
                <label>
                  State
                  <input
                    name="state"
                    required
                    maxLength={50}
                    value={state}
                    disabled={Boolean(startedOrder)}
                    onChange={(e) => setState(e.target.value)}
                  />
                </label>
              </div>
              <p className="checkout-note">
                Delivery PIN ({quote.pincode}) is locked to your shipping quote above.
              </p>
            </div>

            <aside className="checkout-summary" aria-label="Estimated order summary">
              <span className="checkout-summary__eyebrow">YOUR ORDER</span>
              <h3>Estimated total</h3>
              <p className="checkout-summary__product">
                {quote.product_title} — {quote.option_label}
              </p>
              <dl>
                <div>
                  <dt>Product</dt>
                  <dd>{formatPaise(quote.product_paise)}</dd>
                </div>
                <div>
                  <dt>Product GST ({quote.gst_bps / 100}%)</dt>
                  <dd>{formatPaise(quote.product_gst_paise)}</dd>
                </div>
                <div>
                  <dt>Estimated shipping</dt>
                  <dd>{formatPaise(quote.rate_paise)}</dd>
                </div>
                <div>
                  <dt>Shipping GST (18%)</dt>
                  <dd>{formatPaise(quote.shipping_gst_paise)}</dd>
                </div>
                <div className="checkout-summary__total">
                  <dt>Total payable</dt>
                  <dd>{formatPaise(quote.total_paise)}</dd>
                </div>
              </dl>
              <p className="checkout-note">
                {quote.courier_name
                  ? `Lowest available courier: ${quote.courier_name}`
                  : 'Lowest available courier'}
              </p>
              <p className="checkout-note">
                Shipping is an estimate; actual delivery cost may differ. Prepaid only.
              </p>
              <button type="submit" disabled={submitting || finished}>
                {finished
                  ? 'Order paid'
                  : startedOrder
                  ? 'Resume payment'
                  : 'Pay securely with Razorpay'}
              </button>
              <p className="product-checkout__status" role="status" aria-live="polite">
                {status}
              </p>
            </aside>
          </div>
        </form>
      </div>
    </section>
  );
}
