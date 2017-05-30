import React from 'react'

export default class PalletOption extends React.Component {
  render(){
    let option = this.props.option;
    let order = this.props.order;

    return(
      <div
        className="custom-option"
        onClick={()=>{this.props.onSelectOption(order, option)}}
    
      >
        <img className="thumbnail" src={option.iconUrl}/>
      </div>
    )
  }
}
