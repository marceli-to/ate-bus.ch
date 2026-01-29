---
id: home
blueprint: pages
title: Home
template: templates/default
layout: templates/layout
updated_by: ad7fe3d0-f296-4490-a384-26cbf688e223
updated_at: 1769710224
open_graph_type: website
robots: index_follow
elements:
  -
    id: mky2cslg
    image: dummy/bus-mood.jpg
    title: 'Bereit für einen neuen Job?'
    text: 'Die ATE Bus AG ist nicht nur ein sehr fortschrittliches Transportunternehmen im öffentlichen Verkehr, sondern auch ein attraktiver Arbeitgeber.'
    type: hero
    enabled: true
    editor_content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'Die ATE Bus AG ist nicht nur ein sehr fortschrittliches Transportunternehmen im öffentlichen Verkehr, sondern auch ein attraktiver Arbeitgeber.'
    show_content: true
    hero_editor_content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'Die ATE Bus AG ist nicht nur ein sehr fortschrittliches Transportunternehmen im öffentlichen Verkehr, sondern auch ein attraktiver Arbeitgeber.'
  -
    id: mkzpiyu4
    teaser_lost_and_found_title: 'Haben Sie etwas verloren?'
    teaser_lost_and_found_icons:
      - key
      - smartphone
      - purse
      - wallet
      - umbrella
      - headphones
      - glasses
    teaser_lost_and_found_editor_content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'Ist in einem Bus etwas liegen geblieben? Fundgegenstände werden in unserem Betrieb gesammelt. Kontaktieren Sie uns über das Formular.'
    teaser_lost_and_found_link_url: 'entry::fundbuero'
    teaser_lost_and_found_link_title: 'Verlust melden'
    teaser_lost_and_found_link_target: _self
    teaser_lost_and_found_image: dummy/lost-property-mood.jpg
    type: teaser_lost_and_found
    enabled: true
  -
    id: mkzqx8na
    teaser_bus_routes_title: 'Unsere Buslinien'
    teaser_bus_routes_lines:
      -
        id: nWWDOFD0
        type: line
        partner: vbg
        text: 'Verkehrsbetriebe Glattal'
        url: 'https://vbg.ch'
        enabled: true
      -
        id: CLlw4uhb
        type: line
        partner: vbz
        text: 'Verkehrsbetriebe Zürich'
        url: 'https://vbz.ch'
        enabled: true
    teaser_bus_routes_editor_content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'Im Auftrag der VBG Verkehrsbetriebe Glattal und der VBZ Verkehrsbetriebe Zürich sorgen wir täglich für sichere und zuverlässige Mobilität in der Region.'
    teaser_bus_routes_link_url: 'entry::busbetrieb'
    teaser_bus_routes_link_title: 'Buslinien entdecken'
    teaser_bus_routes_link_target: _self
    type: teaser_bus_routes
    enabled: true
show_job_teaser: true
---
