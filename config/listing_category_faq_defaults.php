<?php

/**
 * Default FAQ templates by listing category slug (English).
 * Non-English category rows use slug_aliases to map to the same templates.
 * Edit these or manage per category under Listing Specifications → Categories → FAQ (?).
 */
return [
    'slug_aliases' => [
        'صالون' => 'salon',
        'مستشفى' => 'hospital',
        'يسافر' => 'travel',
        'الفندق' => 'hotel',
        'مطعم' => 'restaurant',
        'أعمال-ال' => 'car',
        'العقارات' => 'real-estate',
        'صالة-للألعاب-الرياضية' => 'gymnasium',
    ],

    'by_slug' => [
        'salon' => [
            ['question' => 'How do I book an appointment?', 'answer' => 'You can book by phone, through our website, or via messaging apps listed on this listing. We recommend booking in advance for weekends and peak hours.'],
            ['question' => 'What safety and hygiene measures do you follow?', 'answer' => 'We follow strict sanitation protocols, use sterilized tools where applicable, and train staff on hygiene best practices.'],
            ['question' => 'What is your cancellation policy?', 'answer' => 'Please cancel or reschedule at least a few hours in advance where possible so we can offer the slot to another client. Late cancellations may be subject to our salon policy.'],
            ['question' => 'Do you offer consultations before services?', 'answer' => 'Yes. We can discuss your goals, skin or hair type, and recommend the right service before you commit.'],
            ['question' => 'What products do you use or recommend?', 'answer' => 'We use professional-grade products suited to each service. Staff can suggest home-care products to maintain your results between visits.'],
        ],
        'hospital' => [
            ['question' => 'How do I book an appointment?', 'answer' => 'You can book through our reception, phone, or online channels listed here. Emergency services follow separate triage procedures.'],
            ['question' => 'What insurance or payment options are accepted?', 'answer' => 'Please contact us for the latest list of accepted insurance and payment methods. Bring valid ID and any referral documents if required.'],
            ['question' => 'What should I bring to my visit?', 'answer' => 'Bring identification, insurance information if applicable, a list of medications, and any prior test results relevant to your visit.'],
            ['question' => 'How can I access my medical records?', 'answer' => 'Requests for records are handled according to privacy regulations. Ask reception or your care team for the correct process and timelines.'],
            ['question' => 'What are your visiting hours?', 'answer' => 'General hours are shown on this listing. Departments may vary; call ahead for specialist clinics or procedures.'],
        ],
        'travel' => [
            ['question' => 'How do I book a trip?', 'answer' => 'Contact us via the details on this listing. We will help you choose dates, itinerary, and any extras, then confirm booking and payment steps.'],
            ['question' => 'Can itineraries be customized?', 'answer' => 'Yes. Trips can be tailored to your budget, interests, and travel dates, subject to availability and supplier rules.'],
            ['question' => 'Do you offer travel insurance?', 'answer' => 'We can guide you on insurance options. Coverage depends on the provider and destination; we recommend suitable protection for your trip.'],
            ['question' => 'What if I need to cancel or change my booking?', 'answer' => 'Fees and deadlines depend on airlines, hotels, and package rules. We will explain the conditions that apply to your booking.'],
            ['question' => 'What documents do I need?', 'answer' => 'Typically a valid passport, visas where required, and any health or entry forms for your destination. We will advise based on your itinerary.'],
        ],
        'hotel' => [
            ['question' => 'What are check-in and check-out times?', 'answer' => 'Standard times are listed on this page. Early check-in or late check-out may be available on request and subject to availability.'],
            ['question' => 'Is parking available?', 'answer' => 'Parking options, fees, and access vary by property. Please ask when booking or at arrival for the latest information.'],
            ['question' => 'Do you allow pets?', 'answer' => 'Pet policies differ by room type and local rules. Contact us before booking to confirm if pets are allowed and any extra charges.'],
            ['question' => 'What amenities are included?', 'answer' => 'Wi‑Fi, housekeeping, and core amenities are described in this listing. Upgrades or add-ons may be available at the front desk.'],
            ['question' => 'How do I modify or cancel a reservation?', 'answer' => 'Use the same channel you booked through or contact us directly. Cancellation terms depend on the rate plan you selected.'],
        ],
        'restaurant' => [
            ['question' => 'Do I need a reservation?', 'answer' => 'Reservations are recommended for busy times and large groups. Walk-ins may be welcome when tables are available.'],
            ['question' => 'Do you cater to dietary requirements?', 'answer' => 'We can often accommodate vegetarian, allergy, or other needs if you tell us when ordering. Ask staff about ingredients if unsure.'],
            ['question' => 'What are your opening hours?', 'answer' => 'Hours on this listing are kept up to date. Holiday hours may differ—please call ahead on special dates.'],
            ['question' => 'Do you offer takeaway or delivery?', 'answer' => 'Options depend on our current service. Check this listing or call us for takeaway, delivery partners, and minimum orders.'],
            ['question' => 'Is private dining or events booking available?', 'answer' => 'We may host private events or groups. Contact us with date, party size, and budget for a tailored quote.'],
        ],
        'car' => [
            ['question' => 'What vehicles do you have in stock?', 'answer' => 'Inventory changes frequently. Browse our listings or contact us with your budget and preferences for the latest availability.'],
            ['question' => 'Can I book a test drive?', 'answer' => 'Yes, in most cases. Bring a valid license and proof of identity. Appointments help us prepare the vehicle for you.'],
            ['question' => 'Do you offer financing or trade-in?', 'answer' => 'Financing and trade-in options depend on partners and approval. Ask our team for current programs and eligibility.'],
            ['question' => 'What warranty or after-sales support is included?', 'answer' => 'Coverage depends on the vehicle and manufacturer. We will explain warranty terms and service options before purchase.'],
            ['question' => 'How do I service my vehicle with you?', 'answer' => 'Book a service slot through our contact channels. We can advise on intervals based on model and usage.'],
        ],
        'real-estate' => [
            ['question' => 'How do I schedule a viewing?', 'answer' => 'Contact us via phone or message on this listing. We will arrange a time and confirm what documents you may need.'],
            ['question' => 'What fees should I expect (buying or renting)?', 'answer' => 'Fees depend on transaction type, location, and regulations. We outline commissions, deposits, and taxes clearly before you commit.'],
            ['question' => 'Do you help with mortgages or legal paperwork?', 'answer' => 'We can introduce trusted partners and guide you through steps, while independent legal and financial advice may still be required.'],
            ['question' => 'How often is your property list updated?', 'answer' => 'We refresh listings regularly. If something is reserved, we will tell you immediately and suggest similar options.'],
            ['question' => 'What areas do you cover?', 'answer' => 'Our focus areas are described on this listing. Ask if you are looking in a specific neighborhood or price range.'],
        ],
        'gymnasium' => [
            ['question' => 'What membership options do you offer?', 'answer' => 'We offer flexible plans (monthly, annual, etc.). Ask for current pricing, joining offers, and what each tier includes.'],
            ['question' => 'Can I try before I join?', 'answer' => 'Trial passes or guest visits may be available. Contact us to check eligibility and schedule a visit.'],
            ['question' => 'What facilities and classes are included?', 'answer' => 'Equipment, classes, and amenities are listed on this page. Premium services may require an add-on or higher tier.'],
            ['question' => 'What are your opening hours?', 'answer' => 'Hours are shown here and may change on holidays. Peak times can be busy—plan your visit accordingly.'],
            ['question' => 'Do you offer personal training?', 'answer' => 'Personal training is often available as an add-on. Ask about packages, trainer availability, and goals.'],
        ],
        'default' => [
            ['question' => 'How can I contact you?', 'answer' => 'Use the phone, email, or messaging options on this listing. We aim to respond during business hours as quickly as possible.'],
            ['question' => 'What are your business hours?', 'answer' => 'Hours are published on this page. If you need an appointment outside normal hours, ask—we may accommodate where possible.'],
            ['question' => 'Where are you located?', 'answer' => 'The address and map on this listing show our location. Contact us if you need parking or landmark directions.'],
            ['question' => 'Do you serve customers in my area?', 'answer' => 'Service areas depend on what we offer. Message us with your location and needs and we will confirm.'],
            ['question' => 'How do I leave feedback or a review?', 'answer' => 'You can leave a review on this platform after your visit, or contact us directly with suggestions or concerns.'],
        ],
    ],
];
