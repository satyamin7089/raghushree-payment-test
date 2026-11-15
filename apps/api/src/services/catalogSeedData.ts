export interface CorePolymerSeedDef {
  slug: string;
  title: string;
  code: string;
  categorySlug: string;
  categoryTitle: string;
  imageSlot: string;
  menuOrder: number;
  shortDesc: string;
  contentHtml: string;
  gradesNotes: string;
  applications: Array<[string, string]>;
  industries: string[];
  processing: Array<[string, string]>;
  faqs: Array<[string, string]>;
  gradesTerms: string[];
  formsTerms: string[];
}

export interface CatalogV2Def {
  slug: string;
  title: string;
  type: 'polymer' | 'pipe';
  products: Array<{
    title: string;
    slug: string;
    imageSlot: string;
    shortDesc: string;
  }>;
}

export function slugify(input: string): string {
  return input
    .toLowerCase()
    .replace(/&/g, ' ')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

export const CATALOG_V2_DEFINITIONS: CatalogV2Def[] = [
  {
    slug: 'pvc-granules',
    title: 'PVC Granules',
    type: 'polymer',
    products: [
      {
        title: 'Rigid PVC Granules',
        slug: 'rigid-pvc-granules',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Rigid PVC compound granules for pipe fittings, rigid profiles, conduits, and technical extrusion lines.',
      },
      {
        title: 'PVC Soft Granules',
        slug: 'pvc-soft-granules',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Plasticized flexible PVC granules for hoses, footwear soles, cable sheathing, gaskets, and flexible tubing.',
      },
    ],
  },
  {
    slug: 'pvc-resin',
    title: 'PVC Resin',
    type: 'polymer',
    products: [
      {
        title: 'PVC K57 Resin',
        slug: 'pvc-k57-resin',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Low-molecular-weight K-57 suspension PVC resin for injection-moulded fittings, transparent sheets, and rigid bottles.',
      },
      {
        title: 'PVC Resin K-70 (Prime)',
        slug: 'pvc-resin-k-70-prime',
        imageSlot: 'polymer_pvc',
        shortDesc: 'High-K-value K-70 prime PVC resin engineered for high-strength cable insulation, flexible hoses, and calendered film.',
      },
      {
        title: 'Off Grade PVC Resin',
        slug: 'off-grade-pvc-resin',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Cost-effective off-grade PVC resin lots suited for utility compounding, conduits, and non-critical extrusion applications.',
      },
    ],
  },
  {
    slug: 'pp-granules',
    title: 'PP Granules',
    type: 'polymer',
    products: [
      {
        title: 'PP Granules',
        slug: 'pp-granules',
        imageSlot: 'polymer_pp',
        shortDesc: 'Polypropylene granules for injection moulding, raffia woven sacks, rigid containers, household goods, and industrial parts.',
      },
    ],
  },
  {
    slug: 'ldpe-granules',
    title: 'LDPE Granules',
    type: 'polymer',
    products: [
      {
        title: 'Mixed Color',
        slug: 'mixed-color',
        imageSlot: 'polymer_ldpe',
        shortDesc: 'Mixed-colour reprocessed LDPE granules for heavy-duty agricultural films, refuse liners, and cost-sensitive extrusion.',
      },
      {
        title: 'LDPE Milky Granules',
        slug: 'ldpe-milky-granules',
        imageSlot: 'polymer_ldpe',
        shortDesc: 'Milky white LDPE granules supporting general packaging films, carry bags, nursery sheets, and flexible liners.',
      },
      {
        title: 'LDPE Natural Granules',
        slug: 'ldpe-natural-granules',
        imageSlot: 'polymer_ldpe',
        shortDesc: 'Natural translucent LDPE granules for high-clarity film extrusion, lamination, and flexible moulded components.',
      },
    ],
  },
  {
    slug: 'hdpe-granules',
    title: 'HDPE Granules',
    type: 'polymer',
    products: [
      {
        title: 'HDPE PE-100 Granules',
        slug: 'hdpe-pe-100-granules',
        imageSlot: 'polymer_hdpe',
        shortDesc: 'High-density PE-100 grade granules for pressure pipe extrusion, industrial conduits, and high-ESCR applications.',
      },
      {
        title: 'HDPE Blue Drum Granules',
        slug: 'hdpe-blue-drum-granules',
        imageSlot: 'polymer_hdpe',
        shortDesc: 'High-molecular-weight reprocessed HDPE blue drum granules for blow-moulded containers, jerrycans, and industrial crates.',
      },
    ],
  },
  {
    slug: 'industrial-hoses',
    title: 'Industrial Hoses',
    type: 'pipe',
    products: [
      {
        title: 'Oil Hoses',
        slug: 'oil-hoses',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Oil-resistant industrial PVC/polymer transfer hoses engineered for workshop, agricultural, and fluid handling duties.',
      },
    ],
  },
  {
    slug: 'pvc-suction-pipes',
    title: 'PVC Suction Pipes',
    type: 'pipe',
    products: [
      {
        title: 'PVC Flexible Suction Pipe',
        slug: 'pvc-flexible-suction-pipe',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Heavy-duty spiral-reinforced PVC flexible suction pipe for agricultural irrigation, dewatering, and industrial fluid transfer.',
      },
      {
        title: 'PVC Duct Pipe',
        slug: 'pvc-duct-pipe',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Lightweight corrugated PVC ducting pipe for industrial ventilation, dust extraction, and fume handling.',
      },
      {
        title: 'Suction Hose PVC Pipe',
        slug: 'suction-hose-pvc-pipe',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Robust PVC suction hose pipe designed for high-vacuum water, slurry, and agricultural pumping systems.',
      },
    ],
  },
  {
    slug: 'pvc-tubing',
    title: 'PVC Tubing',
    type: 'pipe',
    products: [
      {
        title: 'Vipul PVC Flexible & Garden Pipes',
        slug: 'vipul-pvc-flexible-garden-pipes',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Vipul series flexible PVC garden and utility tubing built for domestic watering, nursery irrigation, and light industrial use.',
      },
      {
        title: 'Tiger PVC Flexible Garden Pipes',
        slug: 'tiger-pvc-flexible-garden-pipes',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Tiger series kink-resistant flexible PVC garden pipes offering dependable wall flexibility and outdoor durability.',
      },
      {
        title: 'Raghusree Abir PVC Flexible and Garden Pipes',
        slug: 'raghusree-abir-pvc-flexible-and-garden-pipes',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Raghushree Abir PVC flexible and garden pipes formulated for smooth flow and everyday domestic, farm, and site watering.',
      },
      {
        title: 'Bhavik PVC Flexible & Garden Pipes',
        slug: 'bhavik-pvc-flexible-garden-pipes',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Bhavik series flexible PVC tubing designed for agricultural, construction site, and general water supply.',
      },
    ],
  },
  {
    slug: 'lay-flat-pipes',
    title: 'Lay Flat Pipes',
    type: 'pipe',
    products: [
      {
        title: 'Lay Flat Tubes',
        slug: 'lay-flat-tubes',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Collapsible lay-flat discharge tubes for portable irrigation, dewatering pumps, and space-saving field deployment.',
      },
      {
        title: 'Lay Flat Hoses',
        slug: 'lay-flat-hoses',
        imageSlot: 'polymer_pvc',
        shortDesc: 'Reinforced lay-flat delivery hoses engineered for agricultural water distribution and construction site drainage.',
      },
      {
        title: 'High Pressure Lay Flat Pipe',
        slug: 'high-pressure-lay-flat-pipe',
        imageSlot: 'polymer_pvc',
        shortDesc: 'High-pressure reinforced lay-flat pipe built for demanding pump discharge heads and long-run agricultural delivery.',
      },
    ],
  },
];

export const CORE_POLYMER_LEGACY_DEFS: CorePolymerSeedDef[] = [
  {
    slug: 'polypropylene-pp',
    title: 'Polypropylene (PP)',
    code: 'PP',
    categorySlug: 'pp-granules',
    categoryTitle: 'PP Granules',
    imageSlot: 'polymer_pp',
    menuOrder: 1,
    shortDesc:
      'Versatile thermoplastic polymer supplied for injection moulding, extrusion, raffia/woven packaging, household goods, automotive, and industrial components.',
    contentHtml:
      '<p>Polypropylene (PP) is one of the most widely processed thermoplastics in industrial manufacturing, valued for its balanced stiffness, chemical resistance, fatigue endurance, and processing versatility across moulding and extrusion lines.</p><p>Raghushree supplies Polypropylene raw materials to industrial manufacturers, contract moulders, packaging converters, and distributors across India and international markets. Specific grade selection and form availability are matched to your processing method and end-product requirements upon enquiry.</p>',
    gradesNotes:
      'Available Polypropylene grades and material forms are confirmed against your required melt flow characteristics, colour/natural preference, and processing equipment. Share your target application with our team to verify current grade availability.',
    applications: [
      ['Injection Moulded Components', 'Rigid industrial parts, closures, caps, containers, crates, and technical housings.'],
      ['Raffia, Tape & Woven Packaging', 'Stretched tapes, woven sacks, FIBC bags, and industrial strapping.'],
      ['Household & Consumer Goods', 'Storage articles, furniture components, buckets, and utility ware.'],
      ['Electrical & Automotive Parts', 'Appliance housings, battery casings, and interior moulded components.'],
    ],
    industries: [
      'Plastic Product Manufacturers',
      'Contract Moulding Companies',
      'Packaging & Woven Sack Converters',
      'Household & Consumer Goods Manufacturers',
      'Electrical & Industrial Component Makers',
    ],
    processing: [
      ['Injection Moulding', 'Suitable for thin-wall packaging, general-purpose moulded articles, and structural industrial parts.'],
      ['Extrusion & Tape Stretching', 'Used in raffia tape lines, sheet extrusion, strapping, and monofilament production.'],
      ['Thermoforming', 'Applicable where sheet-grade polypropylene is formed into trays, cups, and packaging.'],
    ],
    faqs: [
      ['How do I confirm which Polypropylene (PP) grade suits my moulding line?', 'Share your part type, mould configuration, and preferred melt flow range in our enquiry form so our team can recommend currently available PP grades.'],
      ['Can I request a PP material sample before placing a bulk order?', 'Yes. Production, R&D, and quality control teams can submit a sample request specifying their application and trial quantity requirements.'],
    ],
    gradesTerms: [],
    formsTerms: ['Plastic Granules'],
  },
  {
    slug: 'high-density-polyethylene-hdpe',
    title: 'High-Density Polyethylene (HDPE)',
    code: 'HDPE',
    categorySlug: 'hdpe-granules',
    categoryTitle: 'HDPE Granules',
    imageSlot: 'polymer_hdpe',
    menuOrder: 2,
    shortDesc:
      'High-strength polyethylene raw material for blow-moulded containers, extruded pipes and conduits, industrial crates, and heavy-duty packaging.',
    contentHtml:
      '<p>High-Density Polyethylene (HDPE) offers a strong strength-to-density ratio, impact resistance, moisture barrier performance, and chemical stability, making it a core raw material across rigid packaging, piping, and industrial moulding sectors.</p><p>Raghushree supports industrial buyers seeking HDPE raw materials for blow moulding, pipe and profile extrusion, injection moulding, and film conversion. Exact grade specifications and material forms are confirmed directly against your production requirements.</p>',
    gradesNotes:
      'HDPE processing requirements differ significantly between blow moulding, pipe extrusion, film extrusion, and injection moulding. Contact our sales team to confirm current availability of specific HDPE grades and forms.',
    applications: [
      ['Blow Moulded Containers', 'Bottles, jerrycans, chemical drums, household liquid containers, and industrial canisters.'],
      ['Pipes, Conduits & Fittings', 'Water supply pipes, agricultural piping, cable ducts, and industrial conduits.'],
      ['Crates, Pallets & Material Handling', 'Heavy-duty injection moulded crates, bins, caps, and closures.'],
      ['HDPE Film & Liners', 'Carry bags, industrial liners, and moisture-barrier packaging.'],
    ],
    industries: [
      'Rigid Packaging Manufacturers',
      'Pipe & Conduit Extruders',
      'Contract Blow & Injection Moulders',
      'Industrial Container Manufacturers',
      'Polymer Distributors & Dealers',
    ],
    processing: [
      ['Blow Moulding', 'Extrusion blow moulding for hollow bottles, jerrycans, drums, and industrial containers.'],
      ['Pipe & Profile Extrusion', 'Continuous extrusion for pressure pipes, conduits, ducts, and sheets.'],
      ['Injection Moulding', 'High-rigidity moulded crates, caps, closures, and industrial pails.'],
      ['Blown Film Extrusion', 'High-strength thin-gauge films and industrial liners.'],
    ],
    faqs: [
      ['Do you supply HDPE for both blow moulding and pipe extrusion?', 'Grade suitability depends on melt flow and density requirements. Please specify whether your line runs blow moulding, extrusion, or injection moulding when submitting your enquiry.'],
      ['How can procurement teams verify current HDPE availability?', 'Submit an availability or quotation request via our contact page or WhatsApp with your required grade specification and estimated quantity.'],
    ],
    gradesTerms: [],
    formsTerms: ['Plastic Granules'],
  },
  {
    slug: 'low-density-polyethylene-ldpe',
    title: 'Low-Density Polyethylene (LDPE)',
    code: 'LDPE',
    categorySlug: 'ldpe-granules',
    categoryTitle: 'LDPE Granules',
    imageSlot: 'polymer_ldpe',
    menuOrder: 3,
    shortDesc:
      'Flexible, tough polyethylene polymer widely used in packaging films, shrink wraps, extrusion coating, squeeze bottles, and flexible moulded parts.',
    contentHtml:
      '<p>Low-Density Polyethylene (LDPE) is characterized by high flexibility, optical clarity in film grades, good toughness, and reliable melt strength during blown and cast film processing as well as extrusion coating.</p><p>Raghushree supplies LDPE raw materials for packaging manufacturers, film extruders, and flexible component moulders. Grade and form availability is verified per order enquiry to ensure compatibility with your extrusion or moulding equipment.</p>',
    gradesNotes:
      'LDPE grades vary by film clarity, slip/additive profile, and melt index. Please share your film thickness or moulding application so we can confirm suitable available LDPE grades and forms.',
    applications: [
      ['Flexible Packaging Films', 'General-purpose packaging bags, pouches, shrink films, and protective wraps.'],
      ['Agricultural & Industrial Liners', 'Greenhouse films, heavy-duty sack liners, and moisture barriers.'],
      ['Extrusion Coating & Lamination', 'Paper, board, and woven fabric coating layers.'],
      ['Flexible Moulded Articles', 'Squeeze bottles, flexible closures, lids, and soft industrial components.'],
    ],
    industries: [
      'Film & Packaging Converters',
      'Flexible Packaging Manufacturers',
      'Lamination & Coating Units',
      'Consumer & Industrial Moulders',
    ],
    processing: [
      ['Blown Film Extrusion', 'Stable bubble formation for general packaging, liners, and shrink films.'],
      ['Cast Film & Extrusion Coating', 'Uniform melt flow for coating substrates and flexible laminates.'],
      ['Injection & Blow Moulding', 'Soft, impact-resistant closures, caps, and squeezable containers.'],
    ],
    faqs: [
      ['What details should film manufacturers include in an LDPE enquiry?', 'Mention your film process (blown or cast), target gauge/micron range, and estimated monthly or batch volume so we can confirm matching LDPE availability.'],
      ['Are domestic and export enquiries supported for LDPE?', 'Yes. Raghushree handles enquiries from manufacturing units across India as well as international buyers.'],
    ],
    gradesTerms: [],
    formsTerms: ['Plastic Granules'],
  },
  {
    slug: 'linear-low-density-polyethylene-lldpe',
    title: 'Linear Low-Density Polyethylene (LLDPE)',
    code: 'LLDPE',
    categorySlug: 'ldpe-granules',
    categoryTitle: 'LDPE Granules',
    imageSlot: 'polymer_lldpe',
    menuOrder: 4,
    shortDesc:
      'High-puncture-resistance polyethylene raw material for stretch film, multilayer packaging, rotational moulding, and durable industrial liners.',
    contentHtml:
      '<p>Linear Low-Density Polyethylene (LLDPE) delivers high tensile strength, puncture resistance, and elongation performance, making it indispensable for stretch films, multilayer flexible packaging, heavy-duty liners, and rotational moulding applications.</p><p>Raghushree works with packaging converters, rotational moulders, and polymer buyers to supply LLDPE in verified grades and forms aligned with their processing method—whether granules for film extrusion or powder/granules where applicable for moulding.</p>',
    gradesNotes:
      'LLDPE may be required in granule form for film/blending lines or specific powder/moulding forms depending on application. Do not assume all forms are stocked simultaneously—please confirm current grade and form availability with our sales desk.',
    applications: [
      ['Stretch & Cling Films', 'Pallet stretch wrap, industrial bundling films, and protective transit films.'],
      ['Multilayer & Blending Films', 'Co-extruded packaging films and LDPE/LLDPE film blends for enhanced toughness.'],
      ['Rotational & Hollow Moulding', 'Water storage tanks, industrial bins, and hollow moulded articles where rotomoulding grades/forms are specified.'],
      ['Cable Jacketing & Tubing', 'Flexible protective sheathing and industrial tubing.'],
    ],
    industries: [
      'Stretch & Multilayer Film Extruders',
      'Flexible Packaging Converters',
      'Rotational & Industrial Moulders',
      'Plastic Raw Material Distributors',
    ],
    processing: [
      ['Blown & Cast Film Extrusion', 'High drawdown capability for thin, puncture-resistant packaging and stretch films.'],
      ['Polymer Blending', 'Frequently blended with LDPE or HDPE to balance film stiffness, sealability, and tear resistance.'],
      ['Rotational & Injection Moulding', 'Applicable when appropriate moulding-grade LLDPE forms are selected.'],
    ],
    faqs: [
      ['Do you supply LLDPE in granules or powder form?', 'Material form availability (such as granules or powder) depends on the specific LLDPE grade currently in supply. Contact us with your process details to verify exact form availability.'],
      ['Can we discuss technical blending requirements with your team?', 'Yes. Production and technical teams can reach out to discuss film extrusion or moulding grade suitability prior to quotation.'],
    ],
    gradesTerms: [],
    formsTerms: ['Plastic Granules', 'Plastic Powder'],
  },
  {
    slug: 'polyvinyl-chloride-pvc',
    title: 'Polyvinyl Chloride (PVC)',
    code: 'PVC',
    categorySlug: 'pvc-resin',
    categoryTitle: 'PVC Resin',
    imageSlot: 'polymer_pvc',
    menuOrder: 5,
    shortDesc:
      'Durable polymer raw material supporting rigid pipes and fittings, profiles, electrical cable insulation, tubing, and industrial extrusion applications.',
    contentHtml:
      '<p>Polyvinyl Chloride (PVC) is a widely utilized engineering and commodity thermoplastic valued for flame retardancy, weatherability, chemical durability, and formulation flexibility across both rigid and flexible applications.</p><p>Raghushree supplies PVC raw materials for manufacturers producing pipes, fittings, profiles, cable sheathing, and industrial components. Available resin/compound grades and forms are confirmed directly per technical enquiry.</p>',
    gradesNotes:
      'PVC processing depends heavily on rigid vs flexible formulation requirements and equipment type. Contact our team with your target specification or K-value/compound need to confirm verified availability.',
    applications: [
      ['Pipes, Conduits & Fittings', 'Potable water pipes, drainage systems, agricultural piping, and electrical conduits.'],
      ['Profiles, Sheets & Building Products', 'Window/door profiles, wall panels, trunking, and industrial sheets.'],
      ['Wire & Cable Insulation', 'Electrical cable sheathing, insulation compounds, and flexible conduits.'],
      ['Flexible Tubing & Industrial Goods', 'Hoses, footwear components, seals, and calendered sheets.'],
    ],
    industries: [
      'Pipe & Fitting Manufacturers',
      'Electrical & Cable Manufacturers',
      'Profile & Building Material Extruders',
      'Industrial Polymer Processors',
    ],
    processing: [
      ['Twin-Screw & Single-Screw Extrusion', 'Rigid pipes, conduits, profiles, flexible hoses, and cable jacketing.'],
      ['Injection Moulding', 'Pipe fittings, electrical junction boxes, valves, and industrial connectors.'],
      ['Calendering & Compounding', 'Sheet production and custom PVC formulation workflows.'],
    ],
    faqs: [
      ['How do I enquire about PVC grades for pipe or cable manufacturing?', 'Specify whether your requirement is for rigid extrusion, fittings moulding, or flexible cable/tubing applications, along with your target specification and order volume.'],
      ['Can distributors enquire for recurring PVC supply?', 'Yes. Dealers, distributors, and repeat manufacturing buyers can submit bulk or distributor enquiries through our contact page.'],
    ],
    gradesTerms: [],
    formsTerms: ['Plastic Granules', 'Plastic Powder'],
  },
  {
    slug: 'polyethylene-terephthalate-pet',
    title: 'Polyethylene Terephthalate (PET)',
    code: 'PET',
    categorySlug: 'pp-granules',
    categoryTitle: 'PP Granules',
    imageSlot: 'polymer_pet',
    menuOrder: 6,
    shortDesc:
      'High-clarity, high-strength polyester polymer used in bottle preforms, thermoformed packaging sheets, strapping tapes, and fibre/extrusion applications.',
    contentHtml:
      '<p>Polyethylene Terephthalate (PET) combines dimensional stability, mechanical strength, gas and moisture barrier properties, and optical clarity, serving beverage/food container preforms, thermoformed packaging, industrial strapping, and textile/fibre extrusion.</p><p>Raghushree handles industrial enquiries for PET raw materials across virgin-compatible and reprocessed/recycled pathways where available. Contact our team to verify available PET grades, intrinsic viscosity (IV) suitability, and material forms.</p>',
    gradesNotes:
      'PET selection depends on target application (preform injection, sheet extrusion, strapping, or fibre) and drying/processing conditions. Reach out to confirm currently available PET grades and forms.',
    applications: [
      ['Preforms & Blow Moulded Bottles', 'Beverage, edible oil, personal care, household, and pharmaceutical containers.'],
      ['Thermoformed Sheets & Trays', 'Blister packs, clamshells, food trays, and transparent rigid packaging.'],
      ['Industrial Strapping & Monofilament', 'High-tensile packaging strapping tapes and technical bristles.'],
      ['Fibre & Sheet Extrusion', 'Non-woven, staple fibre, and industrial sheet applications.'],
    ],
    industries: [
      'Preform & Bottle Manufacturers',
      'Rigid Thermoforming & Packaging Units',
      'Strapping & Extrusion Manufacturers',
      'Industrial Polymer Buyers',
    ],
    processing: [
      ['Injection Stretch Blow Moulding (ISBM)', 'Preform injection followed by biaxial stretch blow moulding.'],
      ['Sheet Extrusion & Thermoforming', 'Transparent rigid sheets formed into trays and blister packaging.'],
      ['Extrusion & Orientation', 'High-tensile PET strapping bands and industrial filaments.'],
    ],
    faqs: [
      ['What technical details help match the right PET grade?', 'Sharing your processing method (preform moulding, sheet thermoforming, or strapping extrusion) and target specification helps our team confirm available PET options.'],
      ['How do I request a PET quotation?', 'Use the Request a Quotation button on this page to pre-select PET on our enquiry form and receive a prompt commercial response.'],
    ],
    gradesTerms: [],
    formsTerms: ['Plastic Granules'],
  },
  {
    slug: 'recycled-reprocessed-polymers',
    title: 'Recycled & Reprocessed Polymers',
    code: 'R-POLY',
    categorySlug: 'ldpe-granules',
    categoryTitle: 'LDPE Granules',
    imageSlot: 'polymer_recycled',
    menuOrder: 7,
    shortDesc:
      'Cost-effective recycled and reprocessed polymer raw materials supplied in verified grades and forms for industrial moulding, extrusion, pipes, and non-food packaging.',
    contentHtml:
      '<p>Recycled and reprocessed polymers enable manufacturers to optimize raw material costs and meet circular material objectives while maintaining dependable processability across injection moulding, extrusion, blow moulding, and utility product manufacturing.</p><p>Raghushree supplies verified reprocessed and recycled polymer grades—primarily in plastic granules and other applicable material forms—matched carefully to each buyer\'s mechanical, colour, and processing requirements.</p>',
    gradesNotes:
      'Reprocessed and recycled polymer availability varies by polymer family (such as PP, HDPE, LDPE, LLDPE, PVC, or PET), colour sorting (natural, white, black, or coloured), filtration mesh, and processing grade. Contact us to check current batch availability and request evaluation samples.',
    applications: [
      ['Industrial & Utility Moulding', 'Crates, pallets, bins, buckets, planters, furniture parts, and material-handling articles.'],
      ['Non-Pressure Pipes, Conduits & Profiles', 'Agricultural pipes, cable ducts, drainage conduits, and extruded profiles.'],
      ['Secondary Packaging & Liners', 'Refuse sacks, construction films, protective wraps, and industrial liners.'],
      ['Compound Blending', 'Controlled blending with primary resins to balance cost and part performance.'],
    ],
    industries: [
      'Contract & Utility Moulders',
      'Pipe, Conduit & Profile Extruders',
      'Industrial & Secondary Packaging Makers',
      'Household & Hardware Product Manufacturers',
      'Plastic Raw Material Dealers',
    ],
    processing: [
      ['Injection Moulding', 'Moulded utility, household, automotive non-critical, and industrial components.'],
      ['Extrusion', 'Pipes, conduits, sheets, profiles, and heavy-duty industrial liners.'],
      ['Blow Moulding', 'Non-food household and industrial containers where reprocessed grades are suitable.'],
    ],
    faqs: [
      ['Can I request a sample of reprocessed or recycled granules for machine trials?', 'Yes. Because reprocessed polymer lots should align with your mould and machine settings, we encourage production and QC teams to request a trial sample and discuss specifications.'],
      ['Are recycled grades available across every polymer and colour at all times?', 'Availability depends on current feedstock and production batches. Please contact our sales team to verify exact polymer family, colour, and grade availability.'],
    ],
    gradesTerms: ['Reprocessed Grades', 'Recycled Grades'],
    formsTerms: ['Plastic Granules'],
  },
];
