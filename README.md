# Landsoukk Realtors — Website

Luxury real estate advisory website for Delhi-NCR (Gurgaon, Delhi, Noida, Greater Noida, Faridabad).

Single-file site (`index.html`): HTML, CSS and vanilla JavaScript, no build step. Served with GitHub Pages.

## Going live checklist
Edit the `CFG` block near the top of the `<script>` in `index.html`:

- `wa` — WhatsApp number, digits with country code (e.g. `9198XXXXXXXX`)
- `phone` — office number for click-to-call
- `email` — enquiry email
- `endpoint` — form/CRM endpoint accepting a JSON POST (Formspree, HubSpot, your API). Until set, forms validate and offer WhatsApp instead of claiming to send.
- `stats` — real business figures only; counters stay hidden while empty
- `social` — profile URLs

Property data lives in the `P` array (all entries are marked as sample listings). Replace with verified inventory.
