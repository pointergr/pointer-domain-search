document.addEventListener("DOMContentLoaded",(()=>{document.querySelectorAll(".wp-block-create-block-pointer-domain-search").forEach((e=>{const t=e.querySelector(".pointer-domain-search-input"),n=e.querySelector(".pointer-domain-search-button"),a=e.querySelectorAll(".pointer-domain-search-tld"),o=e.querySelector(".pointer-domain-search-results"),r=e.querySelector(".pointer-domain-search-loading"),i=e.querySelector(".pointer-domain-search-error"),s=e.getAttribute("data-nonce");function c(){if(!t.value.trim())return void l("Παρακαλούμε εισάγετε ένα όνομα domain.");let n=Array.from(a).filter((e=>e.checked)).map((e=>e.value));if(0===n.length)return void l("Παρακαλούμε επιλέξτε τουλάχιστον ένα TLD.");let c=t.value.trim();const d=c.lastIndexOf(".");if(-1!==d){const t="."+c.substring(d+1).toLowerCase();if(console.log({inputTld:t,selectedTlds:n}),Array.from(a).map((e=>e.value)).includes(t)){if(!n.includes(t)){n.push(t);const o=Array.from(a).find((e=>e.value===t));o&&!o.checked&&(o.checked=!0,function(t){let n=e.querySelector(".pointer-domain-search-notification");n||(n=document.createElement("div"),n.className="pointer-domain-search-notification",e.appendChild(n),n.style.position="absolute",n.style.top="100px",n.style.right="20px",n.style.padding="10px 15px",n.style.backgroundColor="#4CAF50",n.style.color="white",n.style.borderRadius="4px",n.style.boxShadow="0 2px 5px rgba(0,0,0,0.2)",n.style.opacity="0",n.style.transition="opacity 0.3s ease",n.style.zIndex="1000"),n.textContent=t,setTimeout((()=>{n.style.opacity="1"}),10),setTimeout((()=>{n.style.opacity="0"}),3e3)}(`Προστέθηκε αυτόματα το TLD ${t} στην αναζήτηση`))}c=c.substring(0,d)}}o.innerHTML="",i.textContent="",i.classList.remove("active"),n.forEach((e=>{const t=`.${e}`;c.endsWith(t)&&(c=c.slice(0,-t.length))})),r.classList.add("active");const p=new FormData;p.append("action","pointer_domain_search"),p.append("nonce",s),p.append("domain",c),p.append("tlds",JSON.stringify(n)),fetch(wpDomainSearch.ajaxUrl,{method:"POST",credentials:"same-origin",body:p}).then((e=>{if(!e.ok)throw new Error("Network response was not ok");return e.json()})).then((t=>{r.classList.remove("active"),!1!==t.success?function(t){if(!t||0===Object.keys(t).length)return void l("Δεν βρέθηκαν αποτελέσματα.");const n="1"===e.getAttribute("data-show-buy-button"),a=Object.entries(t).map((([e,t])=>`\n                    <div class="pointer-domain-search-result-item">\n                        <strong>${e}</strong>: <span class="${"1"===t||1===t?"pointer-domain-search-result-available":"pointer-domain-search-result-unavailable"}">${"1"===t||1===t?"Διαθέσιμο":"Μη διαθέσιμο"}</span>\n                        ${"1"!==t&&1!==t||!n?"":`<a href="https://www.pointer.gr/domain-names/search?domain-name=${e}" target="_blank" class="pointer-domain-search-buy-button">Αγορά</a>`}\n                    </div>\n                `)).join("");o.innerHTML=a}(t.data):l(t.data||"Υπήρξε ένα σφάλμα κατά την αναζήτηση.")})).catch((e=>{r.classList.remove("active"),l("Υπήρξε ένα σφάλμα κατά την αναζήτηση: "+e.message)}))}function l(e){i.textContent=e,i.classList.add("active")}n.addEventListener("click",(()=>{c()})),t.addEventListener("keypress",(e=>{"Enter"===e.key&&(e.preventDefault(),c())}))}))}));