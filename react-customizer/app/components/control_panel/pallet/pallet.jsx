import React from 'react'
import Subgroup from './subgroup/subgroup.jsx'

export default class Pallet extends React.Component {
  render(){

    let subgroups = this.props.group.subgroups.map((subgroup)=> {
      return(
        <Subgroup
          subgroup={subgroup}
          {...this.props}
          onSelectOption={this.props.onSelectOption}
          key={subgroup.name}
          onMouseOver={this.props.onOptionHover}
        />
      )
    });

    return (
      <div className="pallet">
        {subgroups}
      </div>
    )
  }
}
