import React from 'react';

export default class Description extends React.Component{

  render(){

    return(
      <div className="option-description">
        <div className="description-price">
          Price: + ${this.props.selectedPrice}
        </div>
        <div className="description-header">
          <div className="description-name">
            {this.props.selectedName}
          </div>
        </div>
        <div className="description-info">
          {this.props.selectedDescription}
        </div>
      </div>
    )
  }
}
