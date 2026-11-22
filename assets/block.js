(function (blocks, element, blockEditor, components, serverSideRender) {
  var el = element.createElement;
  var InspectorControls = blockEditor.InspectorControls;
  var useBlockProps = blockEditor.useBlockProps;
  var PanelBody = components.PanelBody;
  var TextControl = components.TextControl;
  var ToggleControl = components.ToggleControl;
  var ServerSideRender = serverSideRender;

  blocks.registerBlockType('scv/products', {
    apiVersion: 2,
    title: 'SCV Product Cards',
    description: 'Renders live Always Available product cards grouped by collection.',
    icon: 'store',
    category: 'widgets',
    attributes: {
      collection: { type: 'string', default: '' },
      otherHeading: { type: 'string', default: 'More Gear' },
      showCountPill: { type: 'boolean', default: true },
      collectionNav: { type: 'boolean', default: false },
      masterHeading: { type: 'string', default: 'Sweatshirts & Hoodies' },
      navLabel: { type: 'string', default: 'Choose collection:' }
    },
    edit: function (props) {
      var attrs = props.attributes;
      var blockProps = useBlockProps({ className: 'scv-products-editor-preview' });
      return [
        el(
          InspectorControls,
          { key: 'inspector' },
          el(
            PanelBody,
            { title: 'Display Settings', initialOpen: true },
            el(ToggleControl, {
              label: 'Group collections under Sweatshirts & Hoodies with filter bar',
              help: 'Shows one master section with collection filter pills above More Gear.',
              checked: !!attrs.collectionNav,
              onChange: function (val) {
                props.setAttributes({ collectionNav: !!val });
              }
            }),
            attrs.collectionNav ? el(TextControl, {
              label: 'Master section heading',
              value: attrs.masterHeading,
              onChange: function (val) {
                props.setAttributes({ masterHeading: val });
              }
            }) : null,
            attrs.collectionNav ? el(TextControl, {
              label: 'Collection filter label',
              help: 'Shown above the collection pills and used as the accessible name of the filter.',
              value: attrs.navLabel,
              onChange: function (val) {
                props.setAttributes({ navLabel: val });
              }
            }) : null,
            el(TextControl, {
              label: 'Filter by Collection slug (optional)',
              help: 'Leave blank to show all collections grouped with headings.',
              value: attrs.collection,
              onChange: function (val) {
                props.setAttributes({ collection: val });
              }
            }),
            el(TextControl, {
              label: 'Heading for uncollected products',
              help: 'Shown above items without a collection (e.g. Hat, Zip Hoodie, Phone Case). Leave blank to omit the heading.',
              value: attrs.otherHeading,
              onChange: function (val) {
                props.setAttributes({ otherHeading: val });
              }
            }),
            el(ToggleControl, {
              label: 'Show color count pill on multi-color cards',
              checked: !!attrs.showCountPill,
              onChange: function (val) {
                props.setAttributes({ showCountPill: !!val });
              }
            })
          )
        ),
        el(
          'div',
          Object.assign({ key: 'preview' }, blockProps),
          el(ServerSideRender, {
            block: 'scv/products',
            attributes: attrs
          })
        )
      ];
    },
    save: function () {
      return null;
    }
  });
})(
  window.wp.blocks,
  window.wp.element,
  window.wp.blockEditor,
  window.wp.components,
  window.wp.serverSideRender
);
