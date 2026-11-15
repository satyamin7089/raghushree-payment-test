import { useState } from 'react';
import { Link } from 'react-router-dom';
import { getSlotImage } from '../lib/images';
import { SectionHeading } from './CommonBlocks';
import { Icon } from './Icon';

const FLIP_CARDS = [
  {
    num: '01',
    slot: 'app_injection',
    title: 'Injection Moulding',
    front:
      'Rigid industrial components, caps and closures, crates, automotive parts, electrical housings, and utility articles.',
    polymers: ['PP', 'HDPE', 'LDPE', 'PET', 'Recycled'],
    details: [
      'Medium-to-high melt flow grades matched to cavity wall thickness',
      'Homopolymer stiffness or impact copolymer toughness options',
      'Free-flowing granules supporting consistent screw recovery',
    ],
    rfqUrl:
      '/contact/?enquiry_type=Technical%20Consultation&application=Injection%20Moulding#enquiry-form',
  },
  {
    num: '02',
    slot: 'app_blow',
    title: 'Blow Moulding',
    front:
      'Hollow bottles, jerrycans, chemical containers, liquid packaging, and industrial drums requiring parison stability.',
    polymers: ['HDPE', 'PET', 'PP', 'LDPE'],
    details: [
      'High melt strength parison control for hollow containers & drums',
      'Strong Environmental Stress Crack Resistance (ESCR) for chemicals',
      'Bottle-grade PET chips & blow-grade polyolefin granules',
    ],
    rfqUrl:
      '/contact/?enquiry_type=Technical%20Consultation&application=Blow%20Moulding#enquiry-form',
  },
  {
    num: '03',
    slot: 'app_extrusion',
    title: 'Extrusion & Pipes',
    front:
      'Water and agricultural piping, electrical conduits, rigid profiles, sheets, raffia tapes, strapping, and cable sheathing.',
    polymers: ['PVC', 'HDPE', 'PP', 'Recycled'],
    details: [
      'Controlled low-MFI pipe pellets & PVC resin/compound options',
      'Granules and powder forms aligned with single or twin-screw lines',
      'Dimensional stability for continuous conduit, profile & tape runs',
    ],
    rfqUrl:
      '/contact/?enquiry_type=Technical%20Consultation&application=Extrusion#enquiry-form',
  },
  {
    num: '04',
    slot: 'app_film',
    title: 'Film & Packaging',
    front:
      'Blown and cast packaging films, stretch wraps, heavy-duty liners, woven sacks, and thermoformed rigid packaging.',
    polymers: ['LLDPE', 'LDPE', 'HDPE', 'PP', 'PET'],
    details: [
      'Balanced bubble stability, optical gloss & puncture resistance',
      'LDPE/LLDPE blend compatibility for thin-gauge drawdown',
      'Confirm slip, antiblock & micron requirements per enquiry',
    ],
    rfqUrl:
      '/contact/?enquiry_type=Technical%20Consultation&application=Film%20%26%20Packaging#enquiry-form',
  },
];

export function FlipCardsSection({
  id = 'manufacturing-applications',
  surface = 'surface--white',
}: {
  id?: string;
  surface?: string;
}) {
  const [flipped, setFlipped] = useState<Record<string, boolean>>({});

  const toggleCard = (num: string, next?: boolean) => {
    setFlipped((prev) => ({
      ...prev,
      [num]: typeof next === 'boolean' ? next : !prev[num],
    }));
  };

  return (
    <section className={`section ${surface}`} id={id}>
      <div className="container">
        <SectionHeading
          eyebrow="Supporting Core Plastic Processing & Conversion Methods"
          title="Useful detail, on the other side."
          description="Select any conversion card below to flip 180 degrees and inspect compatible polymer families, processing checkpoints, and direct enquiry links."
          align="split"
        />

        <div className="flip-grid">
          {FLIP_CARDS.map((card) => {
            const isFlipped = Boolean(flipped[card.num]);
            const img = getSlotImage(card.slot);
            return (
              <article
                key={card.num}
                className={`flip-card reveal${isFlipped ? ' is-flipped' : ''}`}
                onKeyDown={(e) => {
                  if (e.key === 'Escape' && isFlipped) {
                    toggleCard(card.num, false);
                  }
                }}
              >
                <div className="flip-card__inner">
                  {/* FRONT FACE */}
                  <div className="flip-card__face flip-card__front" aria-hidden={isFlipped}>
                    <div
                      className="flip-card__media"
                      onClick={() => toggleCard(card.num, true)}
                    >
                      <span className="flip-card__num">{card.num}</span>
                      <img src={img.src} alt={img.alt} loading="lazy" />
                    </div>
                    <div
                      className="flip-card__body"
                      onClick={() => toggleCard(card.num, true)}
                    >
                      <h3 className="flip-card__title">{card.title}</h3>
                      <p className="flip-card__summary">{card.front}</p>
                    </div>
                    <div className="flip-card__footer">
                      <button
                        type="button"
                        className="flip-card__btn"
                        aria-expanded={isFlipped}
                        aria-label={`${card.title} — Flip card to view technical details`}
                        onClick={() => toggleCard(card.num, true)}
                      >
                        <span>View Technical Detail</span>
                        <Icon name="refresh" />
                      </button>
                    </div>
                  </div>

                  {/* BACK FACE */}
                  <div
                    className="flip-card__face flip-card__back surface--ink"
                    aria-hidden={!isFlipped}
                  >
                    <div className="flip-card__back-top">
                      <span className="flip-card__back-kicker">
                        {card.num} · Conversion Detail
                      </span>
                      <h3 className="flip-card__back-title">{card.title}</h3>

                      <div className="flip-card__tags" aria-label="Compatible polymers">
                        {card.polymers.map((poly) => (
                          <span key={poly} className="flip-card__tag">
                            {poly}
                          </span>
                        ))}
                      </div>

                      <ul className="flip-card__list">
                        {card.details.map((line, i) => (
                          <li key={i}>
                            <span className="flip-card__list-icon" aria-hidden="true">
                              <Icon name="check" />
                            </span>
                            <span>{line}</span>
                          </li>
                        ))}
                      </ul>
                    </div>

                    <div className="flip-card__back-actions">
                      <Link
                        className="btn btn--primary btn--sm btn--full"
                        to={card.rfqUrl}
                        tabIndex={isFlipped ? 0 : -1}
                      >
                        <span>Enquire for This Process</span>
                        <Icon name="arrow-right" className="icon-arrow" />
                      </Link>
                      <button
                        type="button"
                        className="flip-card__return"
                        tabIndex={isFlipped ? 0 : -1}
                        aria-label={`Return to front of card — ${card.title}`}
                        onClick={() => toggleCard(card.num, false)}
                      >
                        <Icon name="arrow-left" />
                        <span>Return to Front</span>
                      </button>
                    </div>
                  </div>
                </div>
              </article>
            );
          })}
        </div>
      </div>
    </section>
  );
}
