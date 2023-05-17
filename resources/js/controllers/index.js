import TomSelect from "tom-select";

export const controllerRelation = () => {
  var selects = document.querySelectorAll('select');
  selects.forEach((select) => {
    const instance = select.tomselect;
    
    if(select.getAttribute('data-maximum-selection-length'))
      instance.settings.maxItems = select.getAttribute('data-maximum-selection-length')
  })

};
