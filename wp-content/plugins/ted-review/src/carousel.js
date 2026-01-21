import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import Glide from '@glidejs/glide';
// se avevi uno stile tuo:
// import './style.scss';

function normalizeNumber(v, def) {
  const n = Number(v);
  return Number.isFinite(n) ? n : def;
}

export default function ReviewsCarousel({
  src,            // URL JSON (obbligatorio)
  limit = 12,
  minRating = 0,
  perView = 3,
  autoplay = 5000, // ms, 0/false per disattivare
}) {
  const [items, setItems] = useState([]);
  const rootRef = useRef(null);
  const glideRef = useRef(null);

  // carica dati
  useEffect(() => {
    let alive = true;
    async function load() {
      if (!src) { setItems([]); return; }
      try {
        const res = await fetch(src, { cache: 'no-store' });
        const data = await res.json();
        const arr = (data.items || [])
          .filter(i => normalizeNumber(i.rating, 0) >= normalizeNumber(minRating, 0))
          .sort((a, b) => String(b.published_at || '').localeCompare(String(a.published_at || '')))
          .slice(0, normalizeNumber(limit, 12));
        if (alive) setItems(arr);
      } catch {
        if (alive) setItems([]);
      }
    }
    load();
    return () => { alive = false; };
  }, [src, limit, minRating]);

  // opzioni glide (memo per non ricrearle ad ogni render)
  const glideOptions = useMemo(() => ({
    type: 'carousel',
    perView: normalizeNumber(perView, 3),
    gap: 16,
    autoplay: normalizeNumber(autoplay, 5000) > 0 ? normalizeNumber(autoplay, 5000) : false,
    hoverpause: true,
    animationDuration: 500,
    breakpoints: {
      1024: { perView: Math.min(2, normalizeNumber(perView, 3)) },
      700:  { perView: 1 }
    }
  }), [perView, autoplay]);

  // init / destroy Glide
  useEffect(() => {
    if (!rootRef.current || items.length === 0) return;
    // distruggi istanza precedente
    if (glideRef.current) {
      try { glideRef.current.destroy(); } catch (_) {}
      glideRef.current = null;
    }
    // crea nuova istanza
    glideRef.current = new Glide(rootRef.current, glideOptions);
    glideRef.current.mount();

    return () => {
      if (glideRef.current) {
        try { glideRef.current.destroy(); } catch (_) {}
        glideRef.current = null;
      }
    };
  }, [items, glideOptions]);

  if (!src) {
    return <div className="ted-review glide"></div>;
  }

  return (
    <div
      ref={rootRef}
      className="ted-review glide"
      aria-label="Reviews carousel"
      data-src={src}
    >
      <div className="glide__track" data-glide-el="track">
        <ul className="glide__slides">
          {items.map((it, idx) => (
            <li className="glide__slide" key={it.id || idx}>
              <article className="erd-card" itemScope itemType="https://schema.org/Review">
                <p itemProp="author">{it.author_name ?? ''}</p>
                <p><strong>★ {Number(it.rating || 0).toFixed(1)}</strong></p>
                <p itemProp="reviewBody">{it.text ?? ''}</p>
                {it.permalink ? (
                  <a href={it.permalink} target="_blank" rel="nofollow noopener">
                    Vedi su {it.source || 'Google'}
                  </a>
                ) : null}
              </article>
            </li>
          ))}
        </ul>
      </div>

      {/* Bullets auto (un bottone per slide) */}
      <div className="glide__bullets" data-glide-el="controls[nav]">
        {items.map((_, i) => (
          <button
            key={i}
            className="glide__bullet"
            data-glide-dir={`=${i}`}
            aria-label={`Vai alla slide ${i + 1}`}
          />
        ))}
      </div>

      {/* Se vuoi le frecce, scommenta:
      <div className="erd-arrows glide__arrows" data-glide-el="controls">
        <button className="glide__arrow glide__arrow--left" data-glide-dir="<" aria-label="Prev">‹</button>
        <button className="glide__arrow glide__arrow--right" data-glide-dir=">" aria-label="Next">›</button>
      </div>
      */}
    </div>
  );
}