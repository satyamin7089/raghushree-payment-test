import { describe, it } from 'node:test';
import assert from 'node:assert/strict';
import {
  buildMasterVariations,
  isValidMeasurement,
  parseDecimalPaise,
  selectLowestShippingRate,
  taxPaise,
} from '../src/utils/pricing.js';

describe('Pricing & Product Master Business Rules', () => {
  it('computes integer-paise GST rounding accurately', () => {
    assert.equal(taxPaise(10000, 1800), 1800); // ₹100 * 18% = ₹18
    assert.equal(taxPaise(3333, 1800), 600); // 3333 * 1800 = 5,999,400 -> +5000 = 6004400 / 10000 = 600
    assert.equal(taxPaise(0, 1800), 0);
    assert.equal(taxPaise(10000, 500), 500);
  });

  it('parses decimal rupee/percentage inputs cleanly', () => {
    assert.equal(parseDecimalPaise('18', 10000), 1800);
    assert.equal(parseDecimalPaise('18.50', 10000), 1850);
    assert.equal(parseDecimalPaise('1250.75', 1000000000), 125075);
    assert.equal(parseDecimalPaise('101', 10000), null);
    assert.equal(parseDecimalPaise('-5', 10000), null);
    assert.equal(parseDecimalPaise('12.345', 1000000), null);
  });

  it('validates shipping measurements (0.001 - 99999)', () => {
    assert.equal(isValidMeasurement('5'), true);
    assert.equal(isValidMeasurement('25.125'), true);
    assert.equal(isValidMeasurement('0'), false);
    assert.equal(isValidMeasurement('-1'), false);
    assert.equal(isValidMeasurement('100000'), false);
    assert.equal(isValidMeasurement('abc'), false);
  });

  it('builds polymer variations and suppresses legacy 30kg pack', () => {
    const vars = buildMasterVariations(
      { product_type: 'polymer', gst_bps: 1800, hsn: '390110', packet_label: '', packet_price_paise: 0 },
      [
        {
          option_key: '5kg',
          variation_kind: 'polymer_pack',
          label: '5 kg',
          quantity: 1,
          price_paise: 75000,
          enabled: true,
          weight_kg: '5.2',
          length_cm: '30',
          width_cm: '25',
          height_cm: '15',
        },
        {
          option_key: '30kg',
          variation_kind: 'legacy_pack',
          label: '30 kg',
          quantity: 1,
          price_paise: 420000,
          enabled: true,
          weight_kg: '30.5',
          length_cm: '50',
          width_cm: '40',
          height_cm: '30',
        },
        {
          option_key: 'sample',
          variation_kind: 'sample',
          label: 'Sample',
          quantity: 1,
          price_paise: 99900, // Even if corrupted, sample price is always 0
          enabled: true,
          weight_kg: '0.5',
          length_cm: '15',
          width_cm: '10',
          height_cm: '5',
        },
      ]
    );

    assert.equal(vars['5kg'].price_paise, 75000);
    assert.equal(vars['5kg'].enabled, true);
    assert.equal(vars['30kg'], undefined);
    assert.equal(vars.sample.price_paise, 0);
    assert.equal(vars.sample.enabled, true);
  });

  it('builds pipe packet variations derived from packet_price_paise * qty', () => {
    const vars = buildMasterVariations(
      {
        product_type: 'pipe',
        gst_bps: 1800,
        hsn: '391723',
        packet_label: '30m coil',
        packet_price_paise: 120000,
      },
      [
        {
          option_key: 'packet_1',
          variation_kind: 'pipe_packet',
          label: '1 30m coil',
          quantity: 1,
          price_paise: 120000,
          enabled: true,
          weight_kg: '8',
          length_cm: '45',
          width_cm: '45',
          height_cm: '18',
        },
        {
          option_key: 'packet_3',
          variation_kind: 'pipe_packet',
          label: '3 30m coil',
          quantity: 3,
          price_paise: 360000,
          enabled: true,
          weight_kg: '24',
          length_cm: '45',
          width_cm: '45',
          height_cm: '54',
        },
      ]
    );

    assert.equal(vars.packet_1.price_paise, 120000);
    assert.equal(vars.packet_3.price_paise, 360000);
    assert.equal(vars.packet_3.label, '3 30m coil');
    assert.equal(vars.packet_2, undefined);
    assert.equal(vars['5kg'], undefined);
  });

  it('selects lowest non-blocked Shiprocket rate and ignores invalid/blocked couriers', () => {
    const selected = selectLowestShippingRate({
      available_courier_companies: [
        { courier_company_id: 101, courier_name: '<b>Blocked Fast</b>', rate: 120, blocked: 1 },
        { courier_company_id: 0, courier_name: 'Zero ID', rate: 100, blocked: 0 },
        { courier_company_id: 202, courier_name: 'Delhivery Surface', rate: 245.5, blocked: 0 },
        { courier_company_id: 303, courier_name: 'BlueDart Air', rate: 310, blocked: 0 },
        { courier_company_id: 404, courier_name: 'Zero Rate', rate: 0, blocked: 0 },
      ],
    });

    assert.deepEqual(selected, {
      courier_id: '202',
      courier_name: 'Delhivery Surface',
      rate_paise: 24550,
    });
  });
});
