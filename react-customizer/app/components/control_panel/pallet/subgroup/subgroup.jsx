import React from 'react'
import PalletOption from './pallet_option.jsx'

export default class Subgroup extends React.Component {

  render(){
    let options = this.props.subgroup['options'].map((option) => {
      return(
        <PalletOption
          {...this.props}
          option={option}
          order={this.props.subgroup.order}
          onSelectOption={this.props.onSelectOption}
          key={option.id}

        />
      );
    });

    return(
      <div className="subgroup">
        <div className="header">{this.props.subgroup['name']}</div>
        <ul className="options">{options}</ul>
      </div>
    );
  }
}
