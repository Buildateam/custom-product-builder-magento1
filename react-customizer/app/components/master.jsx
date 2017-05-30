import React from 'react';
import Header from './header/header.jsx';
import Viewer from './viewer/viewer.jsx'
import ControlPanel from './control_panel/control_panel.jsx'
import AdditionalView from './additional_view/additional_view.jsx'
import data from '../model/guitar_data.js';

export default class Master extends React.Component {
  constructor(props){
    super(props);

    let groups = data.groups;
    this.state = {
      userSelections: [
        0,
        groups[0].subgroups[0]['options'][1],
        groups[0].subgroups[1]['options'][0],
        groups[1].subgroups[0]['options'][4],
        groups[1].subgroups[1]['options'][0],
        groups[1].subgroups[2]['options'][0],
        groups[2].subgroups[0]['options'][0],
        groups[2].subgroups[1]['options'][0],
        groups[2].subgroups[2]['options'][0],
        groups[2].subgroups[3]['options'][0]
      ],
      isRighty: true,
      isZoomed: false,
      isNormal: true,
      priceOfUserSelections: 0,
      selectedName: '',
      selectedDescription: '',
      selectedPrice: 0,

    }
    this.updateToSelectedOptions = this.updateToSelectedOptions.bind(this);
    this.setZoom = this.setZoom.bind(this);
    this.setNormal = this.setNormal.bind(this);
  }

  _setUserSelections(index, obj){
    let newUserSelections = Object.assign([], this.state.userSelections);
    newUserSelections[index] = obj;
    let prices = newUserSelections.map(obj => obj.price);
    prices.shift();
    let priceOfUserSelections = prices.reduce((a,b)=>a+b);
    let isRighty = (newUserSelections[1].id === 'Right');
    this.setState({userSelections: newUserSelections, isRighty: isRighty, priceOfUserSelections: priceOfUserSelections})
    }

  _updateDescription(userOption){
    let selectedName = userOption.name;
    let selectedDescription = userOption.description;
    let selectedPrice =  userOption.price;
    this.setState({selectedName: selectedName, selectedDescription: selectedDescription, selectedPrice: selectedPrice})
  }

  updateToSelectedOptions(subgroupIndex, userOption) {
    this._setUserSelections(subgroupIndex, userOption);
    this._updateDescription(userOption);
  }

  setZoom(){
    this.setState({isZoomed: true, isNormal: false});
  }

  setNormal(){
    this.setState({isNormal: true, isZoomed: false});
  }

  render(){
    return (
      <div className="main">
        <Header
          {...this.props}
          priceOfUserSelections={this.state.priceOfUserSelections}
        />
      <div className="row page">
          <div className="medium-5 columns">
            <Viewer
              layers={this.state.userSelections}
              isRighty={this.state.isRighty}
              isZoomed={this.state.isZoomed}
              isNormal={this.state.isNormal}
            />
          </div>
          <div className="large-1 column align-middle">
            <div className="float-center">
              <AdditionalView
                {...this.props}
                setZoom={this.setZoom}
                setNormal={this.setNormal}
                isZoomed={this.state.isZoomed}
                isNormal={this.state.isNormal}
              />
            </div>
          </div>
          <div className="medium-6 columns" >
            <ControlPanel
              {...this.props}
              onSelectOption={this.updateToSelectedOptions}
              selectedName={this.state.selectedName}
              selectedDescription={this.state.selectedDescription}
              selectedPrice={this.state.selectedPrice}

            />
          </div>
        </div>
      </div>

    )
  }
}
