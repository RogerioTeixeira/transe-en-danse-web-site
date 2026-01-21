import { render } from '@wordpress/element';
import ReviewsCarousel from './carousel.js';
import './style.scss'

function mountAll() {
  document.querySelectorAll('.ted-review-mount[data-props]').forEach((node) => {
    if (node.__mounted) return;
    try {
      const props = JSON.parse(node.getAttribute('data-props') || '{}');
      render(<ReviewsCarousel {...props} />, node);
      node.__mounted = true;
    } catch (e) {
      // props invalide -> non monto
      // (volendo, potresti mostrare un messaggio di errore nel node)
    }
  });
}

document.readyState === 'loading'
  ? document.addEventListener('DOMContentLoaded', mountAll)
  : mountAll();