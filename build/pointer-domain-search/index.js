(()=>{"use strict";var e,r={508:()=>{const e=window.wp.blocks,r=window.wp.i18n,i=window.wp.blockEditor,a=window.ReactJSXRuntime,n=JSON.parse('{"UU":"create-block/pointer-domain-search"}');(0,e.registerBlockType)(n.UU,{edit:function({attributes:e,setAttributes:n}){const{tlds:s}=e;return(0,a.jsx)("div",{...(0,i.useBlockProps)(),children:(0,a.jsxs)("div",{className:"pointer-domain-search-preview",children:[(0,a.jsx)("h4",{children:(0,r.__)("Προεπισκόπηση Αναζήτησης Domain","pointer-domain-search")}),(0,a.jsxs)("div",{className:"pointer-domain-search-form",children:[(0,a.jsxs)("div",{className:"pointer-domain-search-input-wrap",children:[(0,a.jsx)("input",{type:"text",className:"pointer-domain-search-input",placeholder:(0,r.__)("Εισάγετε όνομα domain...","pointer-domain-search"),disabled:!0}),(0,a.jsx)("button",{className:"pointer-domain-search-button",disabled:!0,children:(0,r.__)("Αναζήτηση","pointer-domain-search")})]}),(0,a.jsx)("div",{className:"pointer-domain-search-tlds",children:s.split("|").map(((e,r)=>(0,a.jsxs)("label",{className:"pointer-domain-search-tld-label",children:[(0,a.jsx)("input",{type:"checkbox",checked:0===r,disabled:!0}),".",e]},e)))})]}),(0,a.jsx)("p",{className:"pointer-domain-search-note",children:(0,r.__)("Σημείωση: Αυτή είναι μια προεπισκόπηση.","pointer-domain-search")})]})})}})}},i={};function a(e){var n=i[e];if(void 0!==n)return n.exports;var s=i[e]={exports:{}};return r[e](s,s.exports,a),s.exports}a.m=r,e=[],a.O=(r,i,n,s)=>{if(!i){var o=1/0;for(l=0;l<e.length;l++){for(var[i,n,s]=e[l],t=!0,c=0;c<i.length;c++)(!1&s||o>=s)&&Object.keys(a.O).every((e=>a.O[e](i[c])))?i.splice(c--,1):(t=!1,s<o&&(o=s));if(t){e.splice(l--,1);var d=n();void 0!==d&&(r=d)}}return r}s=s||0;for(var l=e.length;l>0&&e[l-1][2]>s;l--)e[l]=e[l-1];e[l]=[i,n,s]},a.o=(e,r)=>Object.prototype.hasOwnProperty.call(e,r),(()=>{var e={17:0,861:0};a.O.j=r=>0===e[r];var r=(r,i)=>{var n,s,[o,t,c]=i,d=0;if(o.some((r=>0!==e[r]))){for(n in t)a.o(t,n)&&(a.m[n]=t[n]);if(c)var l=c(a)}for(r&&r(i);d<o.length;d++)s=o[d],a.o(e,s)&&e[s]&&e[s][0](),e[s]=0;return a.O(l)},i=globalThis.webpackChunkpointer_domain_search=globalThis.webpackChunkpointer_domain_search||[];i.forEach(r.bind(null,0)),i.push=r.bind(null,i.push.bind(i))})();var n=a.O(void 0,[861],(()=>a(508)));n=a.O(n)})();