import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { getSlotImage } from '../lib/images';
import { Icon } from './Icon';

gsap.registerPlugin(ScrollTrigger);

interface StorySlide {
  num: string;
  kicker: string;
  title: string;
  desc: string;
  points: string[];
  slot: string;
  ctaLabel: string;
  ctaUrl: string;
  rfqUrl: string;
}

const SLIDES: StorySlide[] = [
  {
    num: '01',
    kicker: 'Visual Product Story · Polypropylene (PP)',
    title: 'Polypropylene Engineered for Rigid Moulding & High-Tenacity Raffia',
    desc: 'Balanced stiffness, chemical resistance, and flex-fatigue performance across injection-moulded crates, closures, automotive parts, and stretched woven packaging tapes.',
    points: [
      'Homopolymer & copolymer grade alignment per machine',
      'Supplied in free-flowing granules & verified forms',
      'Pre-bulk sample evaluation & scheduled plant supply',
    ],
    slot: 'polymer_pp',
    ctaLabel: 'Explore Polypropylene',
    ctaUrl: '/products/pp-granules/',
    rfqUrl: '/contact/?material=PP%20Granules&enquiry_type=Request%20a%20Quotation#enquiry-form',
  },
  {
    num: '02',
    kicker: 'Visual Product Story · Polyethylene Family (HDPE · LDPE · LLDPE)',
    title: 'High-Strength Containers, Pressure Conduits & Flexible Packaging Films',
    desc: 'From blow-moulded chemical drums and extruded HDPE pipes to high-clarity LDPE liners and puncture-resistant LLDPE stretch films—matched to your melt flow window.',
    points: [
      'HDPE blow moulding, pipe extrusion & crate grades',
      'LDPE & LLDPE film extrusion and rotomoulding options',
      'Consistent feed form verified prior to commercial dispatch',
    ],
    slot: 'polymer_hdpe',
    ctaLabel: 'Explore Polyethylene Range',
    ctaUrl: '/products/hdpe-pe-100-granules/',
    rfqUrl: '/contact/?material=HDPE%20PE-100%20Granules&enquiry_type=Request%20a%20Quotation#enquiry-form',
  },
  {
    num: '03',
    kicker: 'Visual Product Story · PVC & PET Raw Materials',
    title: 'Infrastructure Conduits, Cable Insulation & High-Clarity Containers',
    desc: 'Dependable PVC resin/compound sourcing for rigid piping, profiles, and wire sheathing alongside crystalline PET grades for preforms, thermoformed sheet, and strapping.',
    points: [
      'Rigid & flexible PVC conversion support across granules & powder',
      'PET bottle, sheet & strapping raw material options',
      'Technical consultation for line temperature & drying setups',
    ],
    slot: 'polymer_pvc',
    ctaLabel: 'Explore PVC & PET',
    ctaUrl: '/products/rigid-pvc-granules/',
    rfqUrl: '/contact/?material=Rigid%20PVC%20Granules&enquiry_type=Request%20a%20Quotation#enquiry-form',
  },
  {
    num: '04',
    kicker: 'Visual Product Story · Recycled & Reprocessed Polymers',
    title: 'Practical Cost-Efficiency & Circular Thermoplastic Feedstock',
    desc: 'Sorted reprocessed and recycled polymer granules helping manufacturers optimize raw material economics across non-food industrial mouldings, utility goods, and conduits.',
    points: [
      'Categorized by base polymer family, colour sort & filtration',
      'Honest batch-by-batch parameter confirmation',
      'Trial sample testing encouraged before full-load commitment',
    ],
    slot: 'polymer_recycled',
    ctaLabel: 'Explore Recycled Polymers',
    ctaUrl: '/products/recycled-reprocessed-polymers/',
    rfqUrl: '/contact/?material=Recycled%20%26%20Reprocessed%20Polymers&enquiry_type=Sample%20Request#enquiry-form',
  },
  {
    num: '05',
    kicker: 'Visual Product Story · Kanpur Hub to Domestic & Export Plants',
    title: 'Organized B2B Raw Material Supply Backed by Commercial Clarity',
    desc: 'Coordinated from General Ganj, Kanpur, Raghushree Plast Products Pvt. Ltd. supports single-load requirements, trial validations, and recurring factory dispatches across India and export markets.',
    points: [
      'Transparent grade, form & packaging confirmation at quotation',
      'Responsive coordination for OEMs, moulders & distributors',
      'Direct RFQ & WhatsApp commercial support desk',
    ],
    slot: 'about_supply',
    ctaLabel: 'View Complete Catalog',
    ctaUrl: '/products/',
    rfqUrl: '/contact/',
  },
];

export function VisualProductStory({ id = 'visual-product-story' }: { id?: string }) {
  const sectionRef = useRef<HTMLElement | null>(null);
  const trackRef = useRef<HTMLDivElement | null>(null);
  const stRef = useRef<ScrollTrigger | null>(null);
  const [activeIndex, setActiveIndex] = useState(0);

  const total = SLIDES.length;

  useEffect(() => {
    const section = sectionRef.current;
    const track = trackRef.current;
    if (!section || !track) return;

    const prefersReduced =
      window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced) return;

    const ctx = gsap.context(() => {
      const mm = gsap.matchMedia();
      mm.add('(min-width: 960px)', () => {
        const tween = gsap.to(track, {
          xPercent: -((total - 1) * 100) / total,
          ease: 'none',
          scrollTrigger: {
            trigger: section,
            pin: true,
            scrub: 0.55,
            start: 'top top',
            end: () => `+=${window.innerHeight * 3.2}`,
            anticipatePin: 1,
            snap: {
              snapTo: 1 / (total - 1),
              duration: { min: 0.18, max: 0.35 },
              ease: 'power1.inOut',
            },
            onUpdate: (self) => {
              const idx = Math.round(self.progress * (total - 1));
              setActiveIndex(Math.min(total - 1, Math.max(0, idx)));
            },
          },
        });
        stRef.current = tween.scrollTrigger || null;
        return () => {
          stRef.current = null;
          tween.kill();
        };
      });
    }, sectionRef);

    return () => {
      ctx.revert();
    };
  }, [total]);

  const goToSlide = (targetIdx: number) => {
    const clamped = Math.min(total - 1, Math.max(0, targetIdx));
    setActiveIndex(clamped);

    const st = stRef.current;
    if (st && window.innerWidth >= 960) {
      const targetScroll = st.start + ((st.end - st.start) * clamped) / (total - 1);
      window.scrollTo({ top: targetScroll, behavior: 'smooth' });
      return;
    }

    if (trackRef.current) {
      const slideWidth = trackRef.current.clientWidth / total;
      trackRef.current.parentElement?.scrollTo({
        left: clamped * slideWidth,
        behavior: 'smooth',
      });
    }
  };

  return (
    <section
      ref={sectionRef}
      className="vps-section surface--ink"
      id={id}
      data-visual-story
      aria-roledescription="carousel"
      aria-label="Five-Slide Visual Polymer Story"
    >
      <div
        className="vps-viewport"
        tabIndex={0}
        onKeyDown={(e) => {
          if (e.key === 'ArrowRight') {
            e.preventDefault();
            goToSlide(activeIndex + 1);
          } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            goToSlide(activeIndex - 1);
          }
        }}
      >
        <div className="vps-track" ref={trackRef}>
          {SLIDES.map((slide, idx) => {
            const img = getSlotImage(slide.slot);
            return (
              <article
                key={slide.num}
                className={`vps-slide${idx === activeIndex ? ' is-active' : ''}`}
                role="group"
                aria-roledescription="slide"
                aria-label={`${idx + 1} of ${total}: ${slide.title}`}
              >
                <div className="vps-slide__bg" aria-hidden="true">
                  <img
                    className="vps-slide__img"
                    src={img.src}
                    alt={img.alt}
                    loading={idx === 0 ? 'eager' : 'lazy'}
                  />
                  <div className="vps-slide__scrim" />
                </div>

                <div className="container vps-slide__container">
                  <div className="vps-slide__content">
                    <div className="vps-slide__meta">
                      <span className="vps-slide__badge">{slide.num} / 05</span>
                      <span className="eyebrow eyebrow--on-dark">{slide.kicker}</span>
                    </div>
                    <h2 className="vps-slide__title">{slide.title}</h2>
                    <p className="vps-slide__desc">{slide.desc}</p>
                    <ul className="vps-slide__points">
                      {slide.points.map((pt, pIdx) => (
                        <li key={pIdx}>
                          <span className="vps-slide__point-icon" aria-hidden="true">
                            <Icon name="check" />
                          </span>
                          <span>{pt}</span>
                        </li>
                      ))}
                    </ul>
                    <div className="vps-slide__actions">
                      <Link className="btn btn--primary" to={slide.ctaUrl}>
                        <span>{slide.ctaLabel}</span>
                        <Icon name="arrow-right" className="icon-arrow" />
                      </Link>
                      <Link className="btn btn--on-dark" to={slide.rfqUrl}>
                        <span>Enquire Now</span>
                      </Link>
                    </div>
                  </div>
                </div>
              </article>
            );
          })}
        </div>

        <div className="vps-controls">
          <div className="container vps-controls__inner">
            <div className="vps-steps" role="tablist" aria-label="Select visual story slide">
              {SLIDES.map((slide, idx) => (
                <button
                  key={slide.num}
                  type="button"
                  className={`vps-step${idx === activeIndex ? ' is-active' : ''}`}
                  role="tab"
                  aria-selected={idx === activeIndex}
                  aria-label={`Go to slide ${slide.num}: ${slide.title}`}
                  onClick={() => goToSlide(idx)}
                >
                  <span className="vps-step__num">{slide.num}</span>
                </button>
              ))}
            </div>

            <div className="vps-progress" aria-hidden="true">
              <div
                className="vps-progress__bar"
                style={{ width: `${((activeIndex + 1) / total) * 100}%` }}
              />
            </div>

            <div className="vps-arrows">
              <button
                type="button"
                className="vps-arrow"
                aria-label="Previous story slide"
                onClick={() => goToSlide(activeIndex - 1)}
              >
                <Icon name="chevron-left" />
              </button>
              <button
                type="button"
                className="vps-arrow"
                aria-label="Next story slide"
                onClick={() => goToSlide(activeIndex + 1)}
              >
                <Icon name="chevron-right" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
