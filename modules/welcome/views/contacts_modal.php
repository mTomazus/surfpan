<h2><span>Contact</span> Details</h2>
<p>If you have questions about SurfPan, competitions, billing or technical issues, contact us using the phone or via email.</p>

<section class="contact-info" style="display:grid;gap:1rem;grid-template-columns:1fr;align-items: center;justify-items: center;text-align: center;font-size: 1.2rem;">
    <li><a href="mailto:info@surfpan.com"><i class="fa fa-envelope" aria-hidden="true"></i> info@surfpan.com</a></li>
    <li><i class="fa fa-globe" aria-hidden="true"></i> https://www.surfpan.com</li>
    <li><a href="tel:+37068602356"><i class="fa fa-phone" aria-hidden="true"></i> +370 686 02356</a></li>
    <li><i class="fa fa-building" aria-hidden="true"></i> VšĮ Banglente</li>
    <li><i class="fa fa-map-marker" aria-hidden="true"></i> Debreceno g. 31 - 49</li>
    <li><i class="fa fa-map-marker" aria-hidden="true"></i> Klaipeda, Lithuania</li>
</section>
<div class="flex mt-1 mb-1">
    <button onClick="closeModal()">Cancel</button>
    <button mx-get="welcome/contact_form" mx-build_modal="contactForm" mx-target=".modal-body" class="btn primary">Contact Us Form</button>
</div>

<style>
    .contact-info li {
        list-style: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        & a {
            text-decoration: none;
        }
    }
    .contact-info i {
        color: var(--menu-hover);
        font-size: 1.5rem;
    }
</style>