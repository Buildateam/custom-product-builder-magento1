import React from 'react';
import Description from './description.jsx'
import Pallet from '../pallet/pallet.jsx'

export default class AccordionContent extends React.Component{
  render(){
    let classes = 'accordion-content';
    if (this.props.isActive) {
      classes += ' accordion-active';
    }
    return(
      <div className={classes} role="tabpanel" data-tab-content>
        <div className="row">
          <div className="small-6 columns">
            <Pallet
              {...this.props}
            />
          </div>
          <div className="small-6 columns">
            <Description
              {...this.props}
            />
          </div>
        </div>
      </div>
    )
  }
}
