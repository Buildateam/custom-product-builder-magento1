import React from 'react';

export default class Header extends React.Component {
  render() {
    return(
      <div className="row top-bar">
        <div className="top-bar-left">
          <ul className="menu expanded">
            <li><a className="guitar-link" href="">Guitars</a></li>
            <li><a className="uke-link" href="">Ukuleles</a></li>
            <li><a className="bass-link" href="">Bass</a></li>
          </ul>
        </div>
        <div className="top-bar-right">
          <ul className="menu">
            <li>Total Price:  ${350 + this.props.priceOfUserSelections}</li>  
          </ul>
        </div>
      </div>
    )
  }
}
